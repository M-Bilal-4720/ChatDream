<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersGroup extends Model
{
    use HasFactory;
    protected $table = 'users_groups';
    protected $fillable = [
        'user_id','group_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    public function scopeWithGroups($query,$user_id){
        return $query->with('group')->where('user_id',$user_id)->latest('created_at')->get();
    }

}
