<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Users;
use App\UserReal;

class RealNameEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;
    protected $userReal;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Users $user, UserReal $user_real)
    {
        $this->user = $user;
        $this->userReal = $user_real;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getUserReal()
    {
        return $this->userReal;
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
