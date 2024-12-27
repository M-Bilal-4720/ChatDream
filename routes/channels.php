<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
//

Broadcast::channel('presence.friends', function ($user) {
    return $user; // Ensure the user is authenticated
});

//Broadcast::channel('friends.{id}', function ($user, $id) {
//    return (int) $user->id === (int) $id;
//});

Broadcast::channel('chatboard.{sender_id}.{receiver_id}', function ($user, $sender_id, $receiver_id) {
    return $user->id === (int) $sender_id || $user->id === (int) $receiver_id;
});

Broadcast::channel('group.{group_id}', function ($user, $group_id) {
    return $user->groups()->where('group_id', $group_id)->exists();
});
Broadcast::channel('deletechat.{userId}.{friendId}', function ($user, $userId, $friendId) {
    // Validate that the user is either the sender or the receiver
    return $user->id == $userId || $user->id == $friendId;
});




