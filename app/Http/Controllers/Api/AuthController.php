<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;


class AuthController extends Controller
{
    //
    public function register(Request $request){
        $registrasiData = $request->all();

        $validate = Validator::make($registrasiData, [
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required',
            'no_telp' => 'required',
        ]);
        if ($validate->fails()) {
            return response(['message'=> $validate->errors()],400);
        }
        $registrasiData['status'] = 0;
        $registrasiData['password'] = bcrypt($request->password);

        $user = User::create($registrasiData);

        return response([
            'message' => 'Register Success',
            'user' => $user
        ],200);
    }

    public function login(Request $request){
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password'=> 'required'
        ]);

        if ($validate->fails()) {
            return response(['message'=> $validate->errors()],400);
        }

        if(!Auth::attempt($loginData)){
            return response(['message'=> 'Invalid Credential'],401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $token = $user->createToken('Authentiucation Token')->accessToken;

        return response([
            'message'=> 'Authenticated',
            'user'=> $user,
            'token_type'=> 'Bearer',
            'access_token'=> $token
        ]);
    }
}