<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:8'
        ]);

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $token = $user->createToken('API Token')->accessToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response([
                'error_message' => 'Invalid login details. Please try again'
            ], 422);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        return response([
            'user' => auth()->user(),
            'token' => $token
        ], 200);
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();

        return response([
            'message' => 'You have been successfully logged out!'
        ], 200);
    }

    public function me(Request $request)
    {
        return $request->user();
    }
}
