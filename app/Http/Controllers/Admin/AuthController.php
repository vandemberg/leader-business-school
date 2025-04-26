<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');


        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (auth('api')->user()->role != User::ROLE_ADMIN) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth('api')->logout();
        Cookie::queue(Cookie::forget('admin_token'));
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);

        // Set secure cookie with the token and 1 day expiration
        $response->cookie(
            'admin_token',
            $token,
            1440, // 24 hours in minutes
            '/',
            parse_url(env('FRONTEND_URL', 'http://localhost:3000'), PHP_URL_HOST),
            true, // secure
            true, // httpOnly
            false, // raw
            'Lax' // sameSite
        );

        return $response;
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth('api')->refresh());
    }
}
