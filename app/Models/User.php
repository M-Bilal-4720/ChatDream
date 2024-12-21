<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
const Directory='storage/user/';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'is_live',
        'password',
        'country','about','gender','date_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'users_groups', 'user_id', 'group_id');
    }
    public function group()
    {
        return $this->belongsToMany(Group::class,   'group_id');
    }
    public function groupmessages()
    {
        return $this->hasMany(GroupMessages::class);
    }


}
