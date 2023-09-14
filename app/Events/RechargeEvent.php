<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RechargeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $userId; //用户id

    protected $scene; //充值场景

    protected $number; //充值数量

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $scene, $number)
    {
        $this->userId = $user_id;
        $this->scene = $scene;
        $this->number = $number;
    }

    /**
     * 取事件参数
     *
     * @return array
     */
    public function getEventParam() : array
    {
        $param = [
            'user_id' => $this->userId,
            'scene' => $this->scene,
            'number' => $this->number,
        ];
        return $param;
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
