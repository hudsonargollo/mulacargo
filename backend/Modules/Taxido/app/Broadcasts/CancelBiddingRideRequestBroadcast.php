<?php

namespace Modules\Taxido\Broadcasts;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Modules\Taxido\Models\RideRequest;

class CancelBiddingRideRequestBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new broadcast instance.
     */
    public function __construct(
        public readonly RideRequest $rideRequest,
        public readonly int $driverId
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver-ride-request-' . $this->driverId),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'cancel.bidding.ride.request';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->rideRequest->id,
            'ride_number' => $this->rideRequest->ride_number,
            'status'      => 'cancelled',
            'type'        => 'cancelled',
        ];
    }
}
