<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{
    public function addFriend(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'friend_id' => 'required|exists:users,id']);
if ($validate->fails()) {
    return response()->json(['status'=>false,'message'=>$validate->errors()],422);
}
        $user_id=Auth::guard('user')->user()->id;
$data=Friend::where('user_id',$user_id)->where('friend_id',$request->friend_id)->first();
if($data){
   return response()->json(['status'=>false,'message'=>'Already Friend'],422);
}
        $friendship = Friend::create([
            'user_id' => $user_id,
            'friend_id' => $request->friend_id,
        ]);
        $friendship2 = Friend::create([
            'user_id' => $request->friend_id,
            'friend_id' => $user_id,
        ]);

        return response()->json(['message' => 'Friend added!'], 200);
    }

    public function getFriends()
    {
        $userId=Auth::guard('user')->user()->id;
        $friends = Friend::where('user_id', $userId)->with('user')->get();
        return response()->json($friends);
    }
    public function getFriendsWithMsg()
    {
        $userId = Auth::guard('user')->user()->id;

        // Fetch friends along with the last message
        $friends = Friend::where('user_id', $userId)
            ->with('user') // Include friend user details
            ->get()
            ->map(function ($friend) use ($userId) {
                // Fetch the last message for this friend
                $lastMessage = DB::table('messages')
                    ->where(function ($query) use ($userId, $friend) {
                        $query->where('sender_id', $userId)
                            ->where('receiver_id', $friend->friend_id);
                    })
                    ->orWhere(function ($query) use ($userId, $friend) {
                        $query->where('receiver_id', $userId)
                            ->where('sender_id', $friend->friend_id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();
                $unreadMessage = DB::table('messages')
                    ->Where(function ($query) use ($userId, $friend) {
                        $query->where('receiver_id', $userId)
                            ->where('sender_id', $friend->friend_id)->where('is_read', Message::UNREAD);
                    })->count();

                // Add last message details to the friend object
                $friend->messages = $lastMessage;
                $friend->unread_message = $unreadMessage;
                $friend->last_message_date = $lastMessage ? $lastMessage->created_at : null;

                return $friend;
            })
            ->sortByDesc('last_message_date') // Order by last message date
            ->values(); // Reindex the collection

        return response()->json(['status'=>true,'message'=>'your friends','friends'=>$friends],200);
    }

    function usersNotInFriendList(Request $request)
    {
        $search = $request->query('search');
        $myUserId = Auth::guard('user')->user()->id;

        $usersNotInList = User::whereNotIn('id', function ($query) use ($myUserId) {
            $query->select('friend_id')
                ->from('friends')
                ->where('user_id', $myUserId);
        })
            ->where('id', '!=', $myUserId)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->get();
        return response()->json(['status' => true, 'message' => 'All users not in friends list', 'user' => $usersNotInList,], 200);
    }

    public function deleteFriend(Request $request ,$id){
        $user_id=Auth::guard('user')->user()->id;
        $data=Friend::where('user_id',$user_id)->where('friend_id',$id)->orWhere('friend_id',$user_id)->where('user_id',$id)->get();
        if(!$data){
            return response()->json(['status'=>false,'message'=>'Friend not found'],404);
        }
        $message=Message::where('user_id',$user_id)->where('friend_id',$id)->orwhere('user_id',$id)->where('friend_id',$user_id)->delete();
        $data->delete();
        return response()->json(['status'=>true,'message'=>'Friend deleted!'],200);
    }
}

