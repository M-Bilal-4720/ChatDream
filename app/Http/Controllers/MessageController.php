<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
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
        $validate = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        //    'file' => 'nullable|file|mimes:audio,jpg,jpeg,png,pdf,doc,docx,mp3,wav,ogg,tmp|max:20480', // Limit file types and size
          ]);
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()], 422);
        }

        $directory = 'storage/messages/';
        $user = Auth::guard('user')->user();
        $message = new Message();
            $message->sender_id = $user->id;
            $message->receiver_id = $request->receiver_id;
            $message->message = $request->message;
            $message->type = Message::TEXT_TYPE;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $message->file_name = $file->getClientOriginalName();
          //  $message->duration=$request->duration;
            $mimeType = mime_content_type($file->getPathname());
            $typeMap = [
                'image/' => Message::IMAGE_TYPE,
                'audio/x-wav' => Message::AUDIO_TYPE,  // Explicit for .wav
                'audio/mpeg' => Message::AUDIO_TYPE,
                'audio/' => Message::AUDIO_TYPE,
                'video/' => Message::VIDEO_TYPE,
                'application/pdf' => Message::FILE_TYPE,
                'application/msword' => Message::FILE_TYPE,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => Message::FILE_TYPE,
                'application/vnd.ms-excel' => Message::FILE_TYPE,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => Message::FILE_TYPE,
                'application/vnd.ms-powerpoint' => Message::FILE_TYPE,
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => Message::FILE_TYPE,
            ];

            // Determine message type based on MIME type
            foreach ($typeMap as $prefix => $messageType) {
                if (str_starts_with($mimeType, $prefix)) {
                    $message->type = $messageType;
                    break;
                }
            }
            $fileName = time() . '_' . $request->file('file')->getClientOriginalName();
            $request->file('file')->move(public_path($directory), $fileName);
            $message->file_url = $directory . $fileName;
        }

        $message->save();
        $message['user'] = User::find($request->receiver_id);
        event(new MessageSent($message));
        return response()->json(['status' => false, 'message' => 'Message sent', 'messages' => $message], 200);
    }

    public function sendMessage1(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:audio,jpg,jpeg,png,mp4,pdf,doc,docx,mp3,wav,audio/wav,ogg|max:20480', // Extended mime types for files
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
     public function messageRead(Request $request)
     {
         $userId = Auth::id();
         $friendId = $request->friend_id;
         // Mark messages from this friend as read
         Message::where('sender_id', $friendId)
             ->where('receiver_id', $userId)
             ->update(['is_read' => Message::READ]);
         return response()->json(['success' => true, 'message' => 'Messages marked as read']);
     }
    public function getMessages($receiverId)
    {
        $messages = Message::where(function ($query) use ($receiverId) {
            $query->where('sender_id', auth()->id())->where('is_deleted_by_sender', false)
                ->where('receiver_id', $receiverId);
        })
            ->orWhere(function ($query) use ($receiverId) {
            $query->where('sender_id', $receiverId)->where('is_deleted_by_receiver', false)
                ->where('receiver_id', auth()->id());
        })->with('user')->get();
        return response()->json($messages, 200);
    }

    public function deleteMessage(Request $request, $messageId)
    {
        $userId =Auth::guard('user')->user()->id;
        $message = Message::findOrFail($messageId);
        $friend_id=$message->receiver_id;

        if ($message->sender_id == $userId) {
            if ($request->has('delete_for_everyone') && $request->delete_for_everyone) {
                $message->delete();
                broadcast(new MessageDeleted($messageId, $userId,  $friend_id))->toOthers();
                return response()->json(['status' => true, 'message' => 'Message deleted for everyone'], 200);
            } else {
                $message->is_deleted_by_sender = true;
            }
        } elseif ($message->receiver_id == $userId) {
            $message->is_deleted_by_receiver = true;
        } else {
            return response()->json(['status' => false, 'message' => 'Unauthorized action'], 403);
        }

        $message->save();

        return response()->json(['status' => true, 'message' => 'Message deleted for you'], 200);
    }

}

