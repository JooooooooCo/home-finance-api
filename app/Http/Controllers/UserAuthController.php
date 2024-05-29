<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|string|min:8'
        ]);

        if($validator->fails()){
            return $this->sendError("Validation Error", 422, $validator->errors());
        }

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);

        $token = $user->createToken('API Token')->accessToken;

        $userResponse = [
            'name' => $user->name,
            'email' => $user->email
        ];

        return $this->sendResponse([
            'user' => $userResponse,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError("Validation Error", 422, $validator->errors());
        }

        if (!auth()->attempt($data)) {
            return $this->sendError("Invalid login details. Please try again", 422);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        $userResponse = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ];

        return $this->sendResponse([
            'user' => $userResponse,
            'token' => $token
        ]);
    }

    public function currentCostCenter(Request $request)
    {
        auth()->user()->update(['current_cost_center_id' => $request->header('X-Tenant-ID')]);

        return $this->sendResponse();
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();

        return $this->sendResponse([], 'You have been successfully logged out!');
    }

    public function details(Request $request)
    {
        return $this->sendResponse($request->user());
    }
}
