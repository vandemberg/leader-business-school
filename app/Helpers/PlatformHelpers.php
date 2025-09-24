<?php

if (!function_exists('current_platform_id')) {
  /**
   * Obtém o platform_id atual do contexto da aplicação
   */
  function current_platform_id(): ?int
  {
    // Primeiro tenta pegar do container da aplicação (definido pelo middleware)
    if (app()->bound('current_platform_id')) {
      return app('current_platform_id');
    }

    // Fallback para usuário autenticado
    if (auth()->check()) {
      $user = auth()->user();

      // Tenta current_platform_id do usuário
      if ($user->current_platform_id) {
        return $user->current_platform_id;
      }

      // Tenta buscar da associação PlatformUser
      $platformUser = App\Models\PlatformUser::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->first();

      if ($platformUser) {
        return $platformUser->platform_id;
      }
    }

    return null;
  }
}

if (!function_exists('current_platform')) {
  /**
   * Obtém a instância da plataforma atual
   */
  function current_platform(): ?App\Models\Platform
  {
    $platformId = current_platform_id();

    if (!$platformId) {
      return null;
    }

    return App\Models\Platform::find($platformId);
  }
}

if (!function_exists('platform_scope')) {
  /**
   * Aplica filtro de plataforma a uma query
   */
  function platform_scope($query, string $column = 'platform_id')
  {
    $platformId = current_platform_id();

    if ($platformId) {
      return $query->where($column, $platformId);
    }

    return $query;
  }
}
