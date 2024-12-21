<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusUpdated implements \Illuminate\Contracts\Broadcasting\ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $is_active;

    // Constructor to initialize the user and their status
    public function __construct(User $user, $is_active)
    {
        $this->user = $user;
        $this->is_active = $is_active;
    }

    // Define the broadcast channel
    public function broadcastOn()
    {
        return new PresenceChannel('presence.friends');
    }

    // Define the data to broadcast
    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'is_active' => $this->is_active,
        ];
    }
}
