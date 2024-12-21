<?php

namespace App\Http\Controllers;

    use App\Events\MessageSent;
    use App\Events\TestEvent;
    use App\Events\UserStatusUpdated;
    use App\Models\User;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;

    class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'name' => 'required|string',
            'phone'=>'required|string|unique:users',
            'image'=>'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($validate->fails()){
            return response()->json(['status'=>false,'message'=>$validate->errors()],422);
        }

        $user =new User();
        if($request->hasFile('image')){
            $imageNmae=time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path(User::Directory), $imageNmae);
            $user->avatar=User::Directory.$imageNmae;
        }
            $user->email = $request->email;
            $user->name = $request->name;
            $user->phone=$request->phone;
            $user->password = Hash::make($request->password);
            $user->save();
        $user['token']  = $user->createToken('user')->plainTextToken;
        return response()->json(['status'=>true,'message'=>'login successfully','data'=>$user], 200);
    }

    public function login(Request $request)
    {
        $validate=Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
if ($validate->fails()){
    return response()->json(['status'=>false,'message'=>$validate->errors()],422);
}
        $user = User::where('email', $request->email)->first();
if(!$user){
    return response()->json(['status'=>false,'message'=>'Invalid Email'],404);
}
        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid Password'], 401);
        }

$user['token']  = $user->createToken('user')->plainTextToken;
        return response()->json(['status'=>true,'message'=>'login successfully','data'=>$user], 200);
    }
    public function getUser(Request $request){
        $search = $request->query('search');
        // Search users by name (case-insensitive)
        $user = User::when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
        })->where('id', '!=', Auth::guard('user')->user()->id)->get()
            ->transform(function ($user) {
            $isFriend = DB::table('friends')
                ->where(function ($query) use ($user) {
                    $query->where('user_id', Auth::guard('user')->user()->id)
                        ->where('friend_id', $user->id);
                })
                ->orWhere(function ($query) use ($user) {
                    $query->where('friend_id', Auth::id())
                        ->where('user_id', $user->id);
                })
                ->exists();

            $user->friend = $isFriend;

            return $user;
        });

        return response()->json(['status'=>true,'message'=>'Searched Users','user'=>$user],200);
    }
        public function getliveUser(Request $request){
            $search = $request->query('search');
            // Search users by name (case-insensitive)
            $user = User::when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })->where('id', '!=', Auth::guard('user')->user()->id)->where('is_live',1)->get();
            return response()->json(['status'=>true,'message'=>'Searched Users','user'=>$user],200);
        }
        public function logout(Request $request) {
            // Get the authenticated user
            $user = Auth::guard('user')->user();

            // Check if user is authenticated
            if ($user) {
                // Delete the current access token

                $user->currentAccessToken()->delete();

                return response()->json(['status' => true, 'message' => 'Logged out'], 200);
            }

            return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
        }
        public function userinfo(Request $request, $id)
        {
            $user=User::find($id);
            return response()->json(['status'=>true,'message'=>'User INfo','user'=>$user],200);
        }
// UserController.php
        public function startStream()
        {
            $user = Auth::guard('user')->user();
            $user->is_live = 1;
            $user->save();
            event(new TestEvent($user));
            return response()->json(['message' => 'User is now live'], 200);
        }

        // Set user live status to 0 (offline)
        public function stopStream()
        {
            $user = Auth::guard('user')->user();
            $user->is_live = 0;
            $user->save();

            return response()->json(['message' => 'User is now offline'], 200);
        }

        public function updateProfile(Request $request)
        {
            $validate=Validator::make($request->all(),[
                'name'=>'required|string',
                'country'=>'required|string',
                'gender'=>'required|in:Male,Female,Other',
                'about'=>'required|string',
                'date_birth'=>'required',
            ]);
            if($validate->fails()){
                return response()->json(['status'=>false,'message'=>$validate->error()],401);
            }
            $id=Auth::guard('user')->user()->id;
            $data=User::find($id);
            $data->name=$request->name;
            $data->country=$request->country;
            $data->gender=$request->gender;
            $data->about=$request->about;
            $data->date_birth=$request->date_birth;
            $data->save();
            $NewData=User::find($id);
            return response()->json(['status'=>true,'message'=>'Profile Updated','data'=>$NewData],200);
        }

        public function updateProfileImage(Request $request){
        $validate=Validator::make($request->all(),[
            'avatar'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($validate->fails()){
            return response()->json(['status'=>false,'message'=>$validate->errors()],401);
        }
        $id=Auth::guard('user')->user()->id;
        $data=User::find($id);
        if($request->hasFile('avatar')){
            if($data->avatar){
                $fileData = public_path( $data->avatar );
                if (file_exists($fileData)) {
                    unlink($fileData) ;
                }
            }
            $imageNmae=time() . '.' . $request->file('avatar')->getClientOriginalExtension();
            $request->file('avatar')->move(public_path(User::Directory), $imageNmae);
            $data->avatar=User::Directory.$imageNmae;
            $data->save();
        }
            $Newdata=User::find($id);
        return response()->json(['status'=>true,'message'=>'Profile Image Updated','data'=>$Newdata],200);
        }

        public function updateStatus(Request $request)
        {
            $user = Auth::guard('user')->user();
            $user->is_active = $request->is_active; // Expecting `is_active` (0 or 1) in the request body
            $user->save();
            broadcast(new UserStatusUpdated($user, $request->is_active));
            return response()->json(['message' => 'Status updated successfully.'], 200);
        }

    }


