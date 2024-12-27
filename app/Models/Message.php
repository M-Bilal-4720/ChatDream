<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    const READ = 1;
    const UNREAD = 0;
    const TEXT_TYPE = 'text';
    const IMAGE_TYPE = 'image';
    const FILE_TYPE = 'file';
    const AUDIO_TYPE = 'audio';
    protected $table = 'messages';
    const VIDEO_TYPE = 'video';
    const LINK_TYPE = 'link';

    protected $fillable = ['sender_id', 'receiver_id','type','file_name','file_url', 'message','is_read','is_deleted_by_sender','is_deleted_by_receiver'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'sender_id');
    }

}
