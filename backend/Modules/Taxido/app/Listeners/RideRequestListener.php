<?php

namespace Modules\Taxido\Listeners;

use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Taxido\Enums\ServiceCategoryEnum;
use Modules\Taxido\Events\RideRequestEvent;
use Modules\Taxido\Services\NotificationService;

class RideRequestListener
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(RideRequestEvent $event): void
    {
        Log::info('inside  RideRequestListener');
        try {
            $rideRequest = $event->rideRequest; 
            Log::info('Ride Request : ' . json_encode($rideRequest));
            $settings = getTaxidoSettings();
            Log::info('Settings : ' . json_encode($settings));
            $isBidding = (bool) ($settings['activation']['bidding'] ?? false);
            Log::info('Is Bidding : ' . json_encode($isBidding));
            $serviceCategoryType = $rideRequest->service_category ? $rideRequest->service_category->type : null;
            Log::info('Service Category Type : ' . $serviceCategoryType);

            $drivers = $rideRequest->drivers;
            Log::info('Drivers : ' . json_encode($drivers));
            $placeholders = [
                'driver_name' => 'Driver',
                'ride_number' => $rideRequest->ride_number,
                'rider_name' => data_get($rideRequest->rider, 'name', 'User'),
                'from_to' => implode(" ➡️ ", $rideRequest->locations),
                'service_name' => $rideRequest->service->name,
                'fare_amount' => $rideRequest->ride_fare,
            ];
            Log::info('Placeholders : ' . json_encode($placeholders));

        
            $this->notificationService->sendToUsers(
                $drivers,
                'ride-status-driver-requested',
                $placeholders,
                [
                    'service_request_id' => (string) $event->rideRequest->id,
                    'type' => 'service_request'
                ]
            );


        } catch (Exception $e) {
            Log::error('RideRequestListener: ' . $e->getMessage());
        }
    }
}
