<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LeverSubmitOrder
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $leverOrder;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($lever_order)
    {
        $this->leverOrder = $lever_order;
    }

    public function getLeverOrder()
    {
        return $this->leverOrder;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
