<?php

namespace Modules\Taxido\Broadcasts;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RideRequestStatusUpdateBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int    $rideRequestId
     * @param  int    $rideId
     * @param  string $status
     */
    public function __construct(
        public readonly int    $rideRequestId,
        public readonly int    $rideId,
        public readonly string $status
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ride-request.' . $this->rideRequestId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.status_update';
    }

    public function broadcastWith(): array
    {
        return [
            'id'      => $this->rideRequestId,
            'ride_id' => $this->rideId,
            'status'  => $this->status,
        ];
    }
}
