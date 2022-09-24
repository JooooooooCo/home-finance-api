<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class UserAuthController extends Controller
{
    /**
      * @OA\Post(
      * path="/api/user-register",
      * tags={"User"},
      * summary="User Register",
      *      @OA\RequestBody(
      *          required=true,
      *          @OA\MediaType(
      *             mediaType="multipart/form-data",
      *             @OA\Schema(
      *                type="object",
      *                required={"name","email", "password", "password_confirmation"},
      *                @OA\Property(property="name", type="string", maxLength=255),
      *                @OA\Property(property="email", type="string", maxLength=255),
      *                @OA\Property(property="password", type="string", minLength=8),
      *                @OA\Property(property="password_confirmation", type="string", minLength=8),
      *             ),
      *          ),
      *          @OA\JsonContent(
      *             type="object",
      *             required={"name","email", "password", "password_confirmation"},
      *             @OA\Property(property="name", type="string", maxLength=255),
      *             @OA\Property(property="email", type="string", maxLength=255),
      *             @OA\Property(property="password", type="string", minLength=8),
      *             @OA\Property(property="password_confirmation", type="string", minLength=8),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Register Successfully",
      *          @OA\JsonContent(
      *             type="object",
      *             @OA\Property(
      *                property="user",
      *                type="object",
      *                @OA\Property(property="name", type="string"),
      *                @OA\Property(property="email", type="string"),
      *             ),
      *             @OA\Property(property="token", type="string"),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=422,
      *          description="Unprocessable Content",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="The given data was invalid."),
      *               @OA\Property(
      *                  property="errors",
      *                  type="object",
      *               ),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
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

        $userResponse = [
            'name' => $user->name,
            'email' => $user->email
        ];

        return response([
            'user' => $userResponse,
            'token' => $token
        ], 200);
    }

    /**
      * @OA\Post(
      * path="/api/user-login",
      * tags={"User"},
      * summary="User Login",
      *     @OA\RequestBody(
      *         required=true,
      *         @OA\MediaType(
      *            mediaType="multipart/form-data",
      *            @OA\Schema(
      *               type="object",
      *               required={"email", "password"},
      *               @OA\Property(property="email", type="string"),
      *               @OA\Property(property="password", type="string")
      *            ),
      *         ),
      *         @OA\JsonContent(
      *               type="object",
      *               required={"email", "password"},
      *               @OA\Property(property="email", type="string"),
      *               @OA\Property(property="password", type="string"),
      *         ),
      *     ),
      *      @OA\Response(
      *          response=200,
      *          description="Login Successfully",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(
      *                  property="user",
      *                  type="object",
      *                  @OA\Property(property="name", type="string"),
      *                  @OA\Property(property="email", type="string"),
      *               ),
      *               @OA\Property(property="token", type="string"),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=422,
      *          description="Invalid login details. Please try again",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Invalid login details. Please try again"),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response([
                'message' => 'Invalid login details. Please try again'
            ], 422);
        }

        $token = auth()->user()->createToken('API Token')->accessToken;


        $userResponse = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email
        ];

        return response([
            'user' => $userResponse,
            'token' => $token
        ], 200);
    }

    /**
      * @OA\Post(
      * path="/api/user-current-cost-center",
      * tags={"User"},
      * summary="User set current Cost Center (tenant)",
      * security={{"bearerAuth":{}}},
      *      @OA\Parameter(
      *          name="X-Tenant-ID",
      *          in="header",
      *          required=true,
      *          description="The cost center ID",
      *          @OA\Schema(type="integer"),
      *      ),
      *      @OA\Response(
      *          response=200,
      *          description="Successfully",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Success"),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
    public function currentCostCenter(Request $request)
    {
        auth()->user()->update(['current_cost_center_id' => $request->header('X-Tenant-ID')]);

        return response([
            'message' => 'Success'
        ], 200);
    }

    /**
      * @OA\Post(
      * path="/api/user-logout",
      * tags={"User"},
      * summary="User Logout",
      * security={{"bearerAuth":{}}},
      *      @OA\Response(
      *          response=200,
      *          description="Logout Successfully",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="You have been successfully logged out!"),
      *          ),
      *      ),
      *      @OA\Response(
      *          response=401,
      *          description="Unauthorized",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="message", type="string", example="Unauthenticated."),
      *          ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * ),
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();

        return response([
            'message' => 'You have been successfully logged out!'
        ], 200);
    }

    /**
      * @OA\Get(
      * path="/api/user-details",
      * summary="Get User Details",
      * tags={"User"},
      * security={{"bearerAuth":{}}},
      *      @OA\Response(
      *          response=200,
      *          description="Return the user details",
      *          @OA\JsonContent(
      *               type="object",
      *               @OA\Property(property="id", type="integer"),
      *               @OA\Property(property="name", type="string"),
      *               @OA\Property(property="email", type="string"),
      *               @OA\Property(property="email_verified_at", type="string"),
      *               @OA\Property(property="created_at", type="string"),
      *               @OA\Property(property="updated_at", type="string"),
      *         ),
      *      ),
      *      @OA\Response(response=400, description="Bad request"),
      *      @OA\Response(response=404, description="Resource Not Found"),
      * )
      *
      * @param  \Illuminate\Http\Request  $request
      * @return mixed
      */
    public function details(Request $request)
    {
        return $request->user();
    }
}
