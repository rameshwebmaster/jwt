<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Validator, Input, Redirect; 

class UserAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => [
            'login', 'register', 'resetPassword'
        ]]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        
        try {
            $token = JWTAuth::attempt($credentials);
            
            if (!$token) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $user = User::where('username', $credentials['username'])->first();
        $now = Carbon::now();
       
       
        return response()->json(compact('token'));
    }

    public function userInfo(Request $request)
    {
        $user = JWTAuth::toUser();
        
        $data = [
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'username' => $user->username,
            'country' => $user->country,
            'birth_date' => $user->birth_date ? $user->birth_date: null,
           
        ];
        return response()->json($data);
    }

     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'email' => 'required|unique:users,email',
            'gender' => 'required|in:1,2',
            'birth_date'=>'required',
            'password'=>'required',
           

        ]);

        //Check whether validation is failed or passed
        if($validator->fails()){
            //Redirect back with validation errors
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $request->except(['avatar']);
        $data['role'] = 'consumer';
       
        $data['password'] = bcrypt($data['password']);
//        $data['birth_date'] = $this->formatDate($data['birth_date']);
        $user = User::create($data);


        return response()->json(compact('token'));'token'=>$token
       // return ['success' => 'user_created_successfully'];
    }
}
