<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\UsersGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function createGroup(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'name'=>'required',
            ]);
        if($validate->fails()){
            return response()->json(['status'=>false,'message'=>$validate->errors()],402);
        }
        $group =new Group();
            $group->name = $request->name;
            $group->created_by = Auth::guard('user')->user()->id;
            $group->save();

        $data =new UsersGroup();
        $data->user_id=Auth::guard('user')->user()->id;
        $data->group_id=$group->id;
        $data->save();
        $group['data']=$data;
        return response()->json(['status'=>false,'message'=>'Group created successfully','data'=>$group],200);
    }

    public function addUserToGroup(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);
        $group->users()->attach($request->user_id);

        return response()->json(['message' => 'User added to group']);
    }

    public function getUserGroup(Request $request){
        $user_id=Auth::guard('user')->user()->id;
        $data=UsersGroup::WithGroups($user_id);
        return response()->json(['status'=>false,'message'=>'All groups of user','data'=>$data],200);
    }

    public function getGroupUser(Request $request ,$id){
        $data=Group::find($id);
        $data['totalusers']=$data->users->count();
        $data['activeusers'] = $data->users()->where('is_active', 1)->count();
        $data['users'] = $data->users();
        return response()->json(['status'=>false,'message'=>'User not found','data'=>$data],200);
    }
    public function userGroup(Request $request){
        $user=Auth::guard('user')->user();
        $groups=$user->groups;
        return response()->json(['status'=>true,'message'=>'User Groups','data'=>$groups],200);
    }
}
