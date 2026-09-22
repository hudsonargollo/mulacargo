<?php

namespace Modules\Taxido\Events;

use Modules\Taxido\Models\RideRequest;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CancelBiddingRideRequestEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(RideRequest $rideRequest)
    {
        $this->rideRequest = $rideRequest;
    }
}
