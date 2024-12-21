<?php

namespace App\Channels;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Auth;

class StreamChannel
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $channel
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\PrivateChannel
     */
    public function join($request, $channel)
    {
        // You can authenticate the channel based on your logic, for example:
        return new PrivateChannel('stream-channel.' . $request->user()->id);
    }
}
