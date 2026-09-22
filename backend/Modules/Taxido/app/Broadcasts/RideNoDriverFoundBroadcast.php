<?php

namespace Modules\Taxido\Broadcasts;

use Modules\Taxido\Models\RideRequest;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class RideNoDriverFoundBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  RideRequest $rideRequest  The cancelled ride request.
     */
    public function __construct(
        public readonly RideRequest $rideRequest
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('rider.' . $this->rideRequest->rider_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.no_driver_found';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_request_id' => $this->rideRequest->id,
            'ride_number'     => $this->rideRequest->ride_number,
            'status'          => 'cancelled',
            'message'         => __('taxido::static.rides.no_driver_available'),
        ];
    }
}
