<?php

namespace Modules\Taxido\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Taxido\Events\CancelBiddingRideRequestEvent;
use Modules\Taxido\Broadcasts\CancelBiddingRideRequestBroadcast;
use Modules\Taxido\Notifications\CancelBiddingRideRequestNotification;
use Modules\Taxido\Models\Driver;
use Modules\Taxido\Services\NotificationService;

class CancelBiddingRideRequestListener
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(CancelBiddingRideRequestEvent $event): void
    {
        try {
            $rideRequest = $event->rideRequest;
            Log::info("CancelBiddingRideRequestListener is triggered for ride request #" . ($rideRequest->id ?? 'null'));
            if (!$rideRequest) {
                return;
            }

            // 1. Reject all pending bids for the ride request
            $rideRequest->bids()->whereNull('status')->update(['status' => 'rejected']);

            // 2. Identify all drivers associated with the ride request:
            // - Drivers who placed a bid
            $driverIdsFromBids = $rideRequest->bids()->pluck('driver_id')->toArray();
            // - Drivers to whom the ride request was sent/offered
            $driverIdsFromDrivers = $rideRequest->drivers()->pluck('driver_id')->toArray();

            // Merge and get unique driver IDs
            $driverIds = array_unique(array_merge($driverIdsFromBids, $driverIdsFromDrivers));
            Log::info("CancelBiddingRideRequestListener: Identified driver IDs associated with ride request: " . json_encode($driverIds));

            if (empty($driverIds)) {
                Log::info("CancelBiddingRideRequestListener: No driver IDs to notify.");
                return;
            }

            $drivers = Driver::whereIn('id', $driverIds)->get();
            Log::info("CancelBiddingRideRequestListener: Found " . $drivers->count() . " driver records in DB to notify: " . json_encode($drivers->pluck('id')->toArray()));

            // 3. Loop over drivers to broadcast websocket and trigger Laravel notifications
            foreach ($drivers as $driver) {
                try {
                    Log::info("CancelBiddingRideRequestListener: Broadcasting CancelBiddingRideRequestBroadcast to driver #{$driver->id}");
                    // Broadcast websocket event
                    broadcast(new CancelBiddingRideRequestBroadcast($rideRequest, (int) $driver->id));

                    // Database/Email Notification
                    $driver->notify(new CancelBiddingRideRequestNotification($rideRequest));

                } catch (Exception $innerEx) {
                    Log::warning("CancelBiddingRideRequestListener: Failed to process driver #{$driver->id}: " . $innerEx->getMessage());
                }
            }

            // 4. Send push notification / SMS / email using NotificationService
            $this->notificationService->sendToUsers(
                $drivers,
                'ride-status-driver-cancelled',
                [
                    'ride_number' => $rideRequest->ride_number,
                ],
                [
                    'ride_request_id' => (string) $rideRequest->id,
                    'type'            => 'cancel_bidding_ride_request'
                ]
            );

        } catch (Exception $e) {
            Log::error("CancelBiddingRideRequestListener Global Error: " . $e->getMessage());
        }
    }
}
