<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\PlatformUser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlatformController extends Controller
{
    /**
     * Lista todas as plataformas do usuário autenticado
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $platforms = $user->platforms()
            ->select('platforms.id', 'platforms.name', 'platforms.slug', 'platforms.brand')
            ->get();

        return response()->json([
            'platforms' => $platforms,
            'current_platform_id' => current_platform_id(),
            'current_platform' => current_platform(),
            'show_selector' => $platforms->count() > 1
        ]);
    }

    /**
     * Troca a plataforma atual do usuário
     */
    public function switch(Request $request)
    {
        $request->validate([
            'platform_id' => 'required|integer|exists:platforms,id'
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $platformId = $request->platform_id;

        // Verifica se o usuário tem acesso a essa plataforma
        $hasAccess = PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'error' => 'Você não tem acesso a esta plataforma'
            ], 403);
        }

        // Atualiza current_platform_id do usuário
        $user->update(['current_platform_id' => $platformId]);

        // Gera novo JWT com o novo platform_id
        $newToken = JWTAuth::claims(['platform_id' => $platformId])->fromUser($user);

        // Invalida o token atual
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Se não conseguir invalidar, continua normalmente
        }

        $platform = Platform::find($platformId);

        $response = response()->json([
            'success' => true,
            'message' => "Plataforma alterada para: {$platform->name}",
            'access_token' => $newToken,
            'platform' => $platform,
            'platform_id' => $platformId
        ]);

        // Define novo cookie com platform_id
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

        return $response;
    }

    /**
     * Troca a plataforma atual do usuário (versão web)
     */
    public function webSwitch(Request $request)
    {
        $request->validate([
            'platform_id' => 'required|integer|exists:platforms,id'
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $platformId = $request->platform_id;

        // Verifica se o usuário tem acesso a essa plataforma
        $hasAccess = PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->exists();

        if (!$hasAccess) {
            return back()->withErrors(['platform' => 'Você não tem acesso a esta plataforma']);
        }

        // Atualiza current_platform_id do usuário
        $user->update(['current_platform_id' => $platformId]);

        $platform = Platform::find($platformId);

        return back()->with('success', "Plataforma alterada para: {$platform->name}");
    }
}
