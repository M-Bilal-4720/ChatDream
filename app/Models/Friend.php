<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;
    protected $table = 'friends';
    protected $fillable = [
        'user_id','friend_id',
    ];
    public function user(){
        return $this->belongsTo(User::class,'friend_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class,'receiver_id'); // Adjust according to your Message model
    }
}
