<?php

namespace App\Http\Middleware;

use App\Models\Platform;
use App\Models\PlatformUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnsurePlatformContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não há usuário autenticado, pula o middleware
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $platformId = $this->getCurrentPlatformId($request);

        // Se não conseguiu determinar platform_id, força um fallback
        if (!$platformId) {
            $platformId = $this->assignDefaultPlatform($user);
        }

        // Valida se a plataforma existe e usuário tem acesso
        if (!$this->validatePlatformAccess($user, $platformId)) {
            $platformId = $this->assignDefaultPlatform($user);
        }

        // Define platform_id no contexto da aplicação
        app()->instance('current_platform_id', $platformId);
        $request->merge(['current_platform_id' => $platformId]);

        return $next($request);
    }

    /**
     * Tenta obter platform_id de múltiplas fontes
     */
    private function getCurrentPlatformId(Request $request): ?int
    {
        // 1. Primeiro tenta do JWT token
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();
            $platformId = $payload->get('platform_id');
            if ($platformId) {
                return (int) $platformId;
            }
        } catch (\Exception $e) {
            // Token JWT não válido ou não presente
        }

        // 2. Tenta do cookie
        $cookiePlatformId = $request->cookie('platform_id');
        if ($cookiePlatformId && is_numeric($cookiePlatformId)) {
            return (int) $cookiePlatformId;
        }

        // 3. Tenta do campo current_platform_id do usuário
        $user = auth()->user();
        if ($user && $user->current_platform_id) {
            return $user->current_platform_id;
        }

        // 4. Busca a plataforma mais recente do usuário
        if ($user) {
            $platformUser = PlatformUser::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($platformUser) {
                return $platformUser->platform_id;
            }
        }

        return null;
    }

    /**
     * Atribui uma plataforma padrão ao usuário
     */
    private function assignDefaultPlatform($user): int
    {
        // Pega a primeira plataforma disponível
        $platform = Platform::first();

        if (!$platform) {
            // Se não há plataformas, cria uma padrão
            $platform = Platform::create([
                'name' => 'Plataforma Principal',
                'slug' => 'principal',
                'brand' => 'LBS'
            ]);
        }

        // Cria associação se não existir
        PlatformUser::firstOrCreate([
            'user_id' => $user->id,
            'platform_id' => $platform->id
        ]);

        // Atualiza o current_platform_id do usuário
        $user->update(['current_platform_id' => $platform->id]);

        return $platform->id;
    }

    /**
     * Valida se usuário tem acesso à plataforma
     */
    private function validatePlatformAccess($user, int $platformId): bool
    {
        // Verifica se a plataforma existe
        if (!Platform::find($platformId)) {
            return false;
        }

        // Verifica se o usuário está associado à plataforma
        return PlatformUser::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->exists();
    }
}
