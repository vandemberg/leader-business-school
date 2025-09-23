<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');


        if (!auth('api')->validate($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $credentials['email'])->first();
        $platformId = $user->current_platform_id;
        if (empty($platformId)) {
            $platformId = PlatformUser::where('user_id', $user->id)->value('platform_id');
        }

        $token = JWTAuth::claims(['platform_id' => $platformId])->fromUser($user);

        if ($user->role != User::ROLE_ADMIN) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => auth('api')->user()
        ]);

        return $response;
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
}
