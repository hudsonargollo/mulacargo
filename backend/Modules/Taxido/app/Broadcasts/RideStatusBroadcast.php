<?php

    namespace Modules\Taxido\Broadcasts;

    use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
    use Modules\Taxido\Models\Ride;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Broadcasting\InteractsWithSockets;
    use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
    use Modules\Taxido\Http\Resources\RideDetailResource;

    class RideStatusBroadcast implements ShouldBroadcastNow
    {
        use Dispatchable, InteractsWithSockets, SerializesModels;

        public function __construct(public $ride) {}

        public function broadcastOn(): array
        {
            return [
                new PrivateChannel('ride-status.' . $this->ride?->id),
            ];
        }

        public function broadcastAs(): string
        {
            return 'ride.status';
        }

        public function broadcastWith(): array
        {
            return (new RideDetailResource($this->ride))->toArray(request());
        }
    }
