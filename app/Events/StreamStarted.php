<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class StreamStarted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $producerId;
    public $userId;

    /**
     * Create a new event instance.
     *
     * @param  int  $producerId
     * @param  int  $userId
     */
    public function __construct($producerId, $userId)
    {
        $this->producerId = $producerId;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel('stream-channel.' . $this->userId); // Broadcast to a private channel for the user
    }

    /**
     * Get the event data to be broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'producerId' => $this->producerId,
            'userId' => $this->userId,
        ];
    }
}
