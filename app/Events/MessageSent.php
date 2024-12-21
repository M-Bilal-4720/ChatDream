<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
use SerializesModels;

public $message;
public function __construct(Message $message)
{
    $message->load('user');
$this->message = $message;

}

public function broadcastOn()
{
// Broadcast to a private channel for the chat between two users
return new PrivateChannel('chatboard.' . $this->message->sender_id .'.' . $this->message->receiver_id);
}


}
