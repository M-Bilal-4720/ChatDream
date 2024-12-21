<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Models\GroupMessages;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupMessagesController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:audio,jpg,jpeg,png,mp4,pdf,doc,docx,mp3,wav,ogg|max:20480', // Extended mime types for files
            'type' => 'required|string|in:text,file,audio,video,image,gallery',
        ]);

        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()], 422);
        }

        $data = new GroupMessages();
        $directory = 'storage/group/messages/';
        $user = Auth::guard('user')->user();

        switch ($request->type) {
            case 'text':
                $data->message = $request->message;
                break;

            case 'audio':
            case 'file':
            case 'video':
                if ($request->hasFile('file')) {
                    $originalName = $request->file('file')->getClientOriginalName();
                    $fileName = time() . '_' . $originalName;
                    $request->file('file')->move(public_path($directory), $fileName);
                    $data->file_name = $originalName;
                    $data->file_url = $directory . $fileName;
                    $data->file_type = $request->type; // Indicate the type of file
                }
                break;

            case 'image':
                if ($request->hasFile('file')) {
                    $fileName = 'image_' . time() . '.' . $request->file('file')->extension();
                    $request->file('file')->move(public_path($directory), $fileName);
                    $data->file_url = $directory . $fileName;
                    $data->file_type = 'image';
                }
                break;

            case 'gallery':
                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $image) {
                        $fileName = 'gallery_' . time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path($directory), $fileName);
                        $galleryImages[] = $directory . $fileName;
                    }
                    $data->file_url = json_encode($galleryImages); // Save multiple images as JSON array
                    $data->file_type = 'gallery';
                }
                break;

        }

        $data->type = $request->type;
        $data->user_id = $user->id;
        $data->group_id = $request->group_id;
        $data->save();
        // Broadcast the message to the group (if using WebSockets)
        $data['user'] = User::find($user->id);
        event(new GroupMessageSent($data));

        return response()->json(['status' => true, 'message' => 'Message sent', 'message' => $data], 200);
    }

    public function fetchGroupMessages($groupId)
    {
        $message = GroupMessages::where('group_id', $groupId)->with('user')->get();
        return response()->json($message);
    }

}
