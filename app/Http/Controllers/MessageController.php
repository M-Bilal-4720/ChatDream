<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'receiver_id'=>'required|exists:users,id',
            'message'=>'nullable|string',
//           'file' => 'nullable|file|mimes:audio,jpg,jpeg,png,pdf,doc,docx,mp3,wav,ogg,tmp|max:20480', // Limit file types and size
            'type' => 'required|string|in:text,file,audio,video,contact,camera,gallery,location',
        ]);
        if($validate->fails()){
            return response()->json(['status'=>false,'message'=>$validate->errors()],422);
        }
        $message =new Message();
        $directory='storage/messages/';
        $user=Auth::guard('user')->user();
        if ($request->type === 'text') {
            $message->message = $request->message;

        }
        if ($request->type === 'audio') {
            $fileName=time().'_'.$request->file('file')->getClientOriginalName();

            $request->file('file')->move(public_path($directory), $fileName);
            $message->file_name='Audio';
            $message->file_url=$directory.$fileName;
        }
        if ($request->type === 'file') {
            $fileName=time().'_'.$request->file('file')->getClientOriginalName();
            $request->file('file')->move(public_path($directory), $fileName);
            $message->file_name=$fileName;
            $message->file_url=$directory.$fileName;
        }
        $message->type=$request->type;
        $message->sender_id = $user->id;
        $message->receiver_id = $request->receiver_id;
        $message->save();
        $message['user']=User::find($request->receiver_id);
        event(new MessageSent($message));
        return response()->json(['status'=>false,'message' => 'Message sent','messages'=>$message], 200);
    }
    public function sendMessage1(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:audio,jpg,jpeg,png,mp4,pdf,doc,docx,mp3,wav,ogg|max:20480', // Extended mime types for files
            'type' => 'required|string|in:text,file,audio,video,image,gallery',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()], 422);
        }

        $message = new Message();
        $directory = 'storage/messages/';
        $user = Auth::guard('user')->user();

        switch ($request->type) {
            case 'text':
                $message->message = $request->message;
                break;

            case 'audio':
            case 'file':
            case 'video':
                if ($request->hasFile('file')) {
                    $originalName = $request->file('file')->getClientOriginalName();
                    $fileName = time() . '_' . $originalName;
                    $request->file('file')->move(public_path($directory), $fileName);
                    $message->file_name = $originalName;
                    $message->file_url = $directory . $fileName;
                    $message->file_type = $request->type; // Indicate the type of file
                }
                break;

            case 'image':
                if ($request->hasFile('file')) {
                    $fileName = 'image_' . time() . '.' . $request->file('file')->extension();
                    $request->file('file')->move(public_path($directory), $fileName);
                    $message->file_url = $directory . $fileName;
                    $message->file_type = 'image';
                }
                break;

            case 'gallery':
                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $image) {
                        $fileName = 'gallery_' . time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path($directory), $fileName);
                        $galleryImages[] = $directory . $fileName;
                    }
                    $message->file_url = json_encode($galleryImages); // Save multiple images as JSON array
                    $message->file_type = 'gallery';
                }
                break;

        }

        $message->type = $request->type;
        $message->sender_id = $user->id;
        $message->receiver_id = $request->receiver_id;
        $message->save();

        // Load the receiver user details
        $message['user'] = User::find($request->receiver_id);

        // Fire message sent event
        event(new MessageSent($message));

        return response()->json(['status' => true, 'message' => 'Message sent', 'messages' => $message], 200);
    }

    public function getMessages($receiverId)
    {

//        $user = Friend::where(function ($query) use ($receiverId) {
//            $query->where('user_id', auth()->id())
//                ->where('friend_id', $receiverId);
//        })->orWhere(function ($query) use ($receiverId) {
//            $query->where('user_id', $receiverId)
//                ->where('friend_d', auth()->id());
//        })->get();
//        if(!$user){
//            return response()->json(['status'=>false,'message'=>'Friend not found'],404);
//        }

        $messages = Message::where(function ($query) use ($receiverId) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', auth()->id());
        })->with('user')->get();


        return response()->json($messages, 200);
    }
}

