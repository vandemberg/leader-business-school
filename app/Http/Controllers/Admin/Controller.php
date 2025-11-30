<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Video;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Controller extends BaseController
{
    /**
     * Obtém o platform_id atual do contexto da requisição
     *
     * Prioridade:
     * 1. Do request (se enviado como parâmetro)
     * 2. Do contexto da aplicação (definido pelo middleware EnsurePlatformContext)
     * 3. Do usuário autenticado (current_platform_id)
     *
     * @param Request|null $request
     * @return int|null
     */
    protected function getPlatformId(?Request $request = null): ?int
    {
        // 1. Tenta pegar do request se fornecido
        if ($request && $request->has('platform_id')) {
            $platformId = $request->get('platform_id');
            if (is_numeric($platformId)) {
                return (int) $platformId;
            }
        }

        // 2. Usa a função helper que obtém do contexto da aplicação
        // (definido pelo middleware EnsurePlatformContext)
        if (function_exists('current_platform_id')) {
            $platformId = current_platform_id();
            if ($platformId !== null) {
                return $platformId;
            }
        }

        // 3. Fallback: tenta do usuário autenticado
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && $user->current_platform_id) {
                return $user->current_platform_id;
            }
        }

        return null;
    }

    /**
     * Valida se um recurso pertence à plataforma do usuário atual
     *
     * @param mixed $resource Recurso a validar (deve ter propriedade platform_id)
     * @param Request|null $request
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function validatePlatformAccess($resource, ?Request $request = null): void
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        // Se o recurso tem platform_id direto
        if (isset($resource->platform_id)) {
            // Permite null para compatibilidade com recursos antigos
            if ($resource->platform_id !== null && $resource->platform_id !== $platformId) {
                abort(403, 'Recurso não pertence à sua plataforma');
            }
        }
    }

    /**
     * Valida se um recurso pertence à plataforma através de um Course
     *
     * @param mixed $resource Recurso que pertence a um Course (Module ou Video)
     * @param \App\Models\Course $course Course relacionado
     * @param Request|null $request
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function validatePlatformAccessThroughCourse($resource, $course, ?Request $request = null): void
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        // Garantir que o course tem platform_id carregado
        if (!isset($course->platform_id)) {
            $course->refresh();
        }

        if ($course->platform_id !== null && $course->platform_id !== $platformId) {
            abort(403, 'Recurso não pertence à sua plataforma');
        }
    }

    /**
     * Valida se um Course pertence à plataforma do usuário
     *
     * @param \App\Models\Course $course
     * @param Request|null $request
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function validateCoursePlatform($course, ?Request $request = null): void
    {
        $this->validatePlatformAccess($course, $request);
    }

    /**
     * Obtém a lista de vídeos disponíveis para a plataforma informada.
     *
     * @param int|null $platformId
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function getPlatformVideoIds(?int $platformId): Collection
    {
        $query = Video::query();

        if ($platformId) {
            $query->where(function ($videoQuery) use ($platformId) {
                $videoQuery
                    ->whereHas('course', function ($courseQuery) use ($platformId) {
                        $courseQuery->where(function ($inner) use ($platformId) {
                            $inner
                                ->where('platform_id', $platformId)
                                ->orWhereNull('platform_id');
                        });
                    })
                    ->orWhere(function ($videoQuery) use ($platformId) {
                        $videoQuery
                            ->whereNull('course_id')
                            ->where(function ($inner) use ($platformId) {
                                $inner
                                    ->where('platform_id', $platformId)
                                    ->orWhereNull('platform_id');
                            });
                    });
            });
        }

        return $query->pluck('id');
    }
}
