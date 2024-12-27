<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MediaSoupController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMessagesController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:user')->post('/start-stream', [AuthController::class, 'startStream']);
Route::middleware('auth:user')->post('/stop-stream', [AuthController::class, 'stopStream']);
Route::middleware('auth:user')->group(function () {
    Route::get('/user', function () {
        return auth()->user();
    });

    Route::get('/users', [AuthController::class, 'getUser']);
    Route::get('/live-users', [AuthController::class, 'getliveUser']);
    Route::post('/user/active/status', [AuthController::class, 'updateStatus']);
    Route::get('/user/not/friends',[FriendController::class, 'usersNotInFriendList']);
    //friends
    Route::get('/friends', [FriendController::class, 'getFriends']);
    Route::post('/friends/add', [FriendController::class, 'addFriend']);
    Route::delete('/friends/remove/{id}', [FriendController::class, 'deleteFriend']);
    //Messages
    Route::get('/messages/{friendId}', [MessageController::class, 'getMessages']);
    Route::post('/message/send', [MessageController::class, 'sendMessage']);
    Route::post('/message/send1', [MessageController::class, 'sendMessage1']);
    Route::post('/messages/mark/as/read', [MessageController::class, 'messageRead']);
    Route::delete('/messages/delete/{id}', [MessageController::class, 'deleteMessage']);
    //profile
    Route::post('/update/profile', [AuthController::class, 'updateProfile']);
    Route::post('/update/profile/image', [AuthController::class, 'updateProfileImage']);
    //Groups
    Route::get('/user/groups', [GroupController::class, 'getUserGroup']);
    Route::post('/user/group/create', [GroupController::class, 'createGroup']);
    Route::get('/get/user/groups', [GroupController::class, 'userGroup']);
    Route::post('/user/group/adduser/{id}', [GroupController::class, 'addUserToGroup']);
//Group Messages

    Route::get('/group/messages/{id}', [GroupMessagesController::class, 'fetchGroupMessages']);
    Route::post('/group/message/sent',[GroupMessagesController::class, 'sendMessage']);
Route::get('/group/users/{id}',[GroupController::class, 'getGroupUser']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/user/{id}', [AuthController::class, 'userinfo']);
    Route::get('/friends_with_msg', [FriendController::class, 'getFriendsWithMsg']);
//mediasoup-controller
    Route::post('/webrtc/create-transport', [StreamController::class, 'createTransport']);
    Route::post('/webrtc/connect-transport', [StreamController::class, 'connectTransport']);
    Route::post('/webrtc/produce', [StreamController::class, 'produce']);
    Route::post('/webrtc/consume', [StreamController::class, 'consume']);
    Route::post('/webrtc/pause-consumer', [StreamController::class, 'pauseConsumer']);
    Route::post('/webrtc/resume-consumer', [StreamController::class, 'resumeConsumer']);

});
