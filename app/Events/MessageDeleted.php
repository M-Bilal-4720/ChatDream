<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use SerializesModels;

    public $messageId;
    public $userId;
    public $friendId;

    public function __construct($messageId, $userId, $friendId)
    {
        $this->messageId = $messageId;
        $this->userId = $userId;
        $this->friendId = $friendId;
    }

    public function broadcastOn()
    {
        // Use private channel format for the user and their friend
        return new Channel("deletechat.{$this->userId}.{$this->friendId}");
    }

    public function broadcastAs()
    {
        return 'message.deleted';
    }
}
