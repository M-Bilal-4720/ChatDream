<?php

namespace App\Events;

use App\Models\GroupMessages;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class GroupMessageSent implements ShouldBroadcast
{
    public $message;

    public function __construct(GroupMessages $message)
    {
        $message->load('user');
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('group.' . $this->message->group_id);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}

