<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\PlatformUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private User $user;

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth('api')->validate($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $this->user = User::where('email', $credentials['email'])->first();

        if ($this->user->role != User::ROLE_ADMIN) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Garantir que o usuário sempre tenha uma plataforma
        $platformId = $this->ensureUserPlatform($this->user);

        // Criar JWT com platform_id incluído
        $token = JWTAuth::claims(['platform_id' => $platformId])->fromUser($this->user);


        // Retornar response com token e cookie
        return $this->respondWithToken($token, $platformId);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token, $platformId = null)
    {
        $response = response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $this->user,
            'platform_id' => $platformId
        ]);

        // Define cookie com platform_id se fornecido
        if ($platformId) {
            $response->cookie(
                'platform_id',
                $platformId,
                1440, // 24 hours
                '/',
                parse_url(env('FRONTEND_URL', 'http://localhost:3000'), PHP_URL_HOST),
                true, // secure
                true, // httpOnly
                false, // raw
                'Lax' // sameSite
            );
        }

        return $response;
    }

    public function refresh(): JsonResponse
    {
        $user = auth('api')->user();
        $platformId = $this->ensureUserPlatform($user);

        $token = JWTAuth::refresh(JWTAuth::getToken());

        return $this->respondWithToken($token, $platformId);
    }

    /**
     * Garante que o usuário sempre tenha uma plataforma válida
     */
    private function ensureUserPlatform(User $user): int
    {
        // 1. Verifica current_platform_id do usuário
        if ($user->current_platform_id) {
            $platform = Platform::find($user->current_platform_id);
            if ($platform && $this->userHasPlatformAccess($user, $user->current_platform_id)) {
                return $user->current_platform_id;
            }
        }

        // 2. Busca a plataforma mais recente do usuário
        $platformUser = PlatformUser::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($platformUser) {
            $platform = Platform::find($platformUser->platform_id);
            if ($platform) {
                // Atualiza current_platform_id do usuário
                $user->update(['current_platform_id' => $platformUser->platform_id]);
                return $platformUser->platform_id;
            }
        }

        // 3. Se não encontrou nenhuma, cria associação com plataforma padrão
        return $this->assignDefaultPlatform($user);
    }

    /**
     * Verifica se usuário tem acesso à plataforma
     */
    private function userHasPlatformAccess(User $user, int $platformId): bool
    {
        return PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->exists();
    }

    /**
     * Atribui plataforma padrão ao usuário
     */
    private function assignDefaultPlatform(User $user): int
    {
        // Pega a primeira plataforma disponível ou cria uma
        $platform = Platform::first();

        if (!$platform) {
            $platform = Platform::create([
                'name' => 'Plataforma Principal',
                'slug' => 'principal',
                'brand' => 'LBS'
            ]);
        }

        // Cria associação
        PlatformUser::firstOrCreate([
            'user_id' => $user->id,
            'platform_id' => $platform->id
        ]);

        // Atualiza current_platform_id do usuário
        $user->update(['current_platform_id' => $platform->id]);

        return $platform->id;
    }
}
