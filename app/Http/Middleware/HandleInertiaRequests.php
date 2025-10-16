<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'platform' => fn() => $this->getPlatformData($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }

    /**
     * Get platform data for the authenticated user
     */
    private function getPlatformData(Request $request): ?array
    {
        if (!$request->user()) {
            return null;
        }

        $user = $request->user();
        $platforms = $user->platforms()
            ->select('platforms.id', 'platforms.name', 'platforms.slug', 'platforms.brand')
            ->get();

        return [
            'current' => current_platform(),
            'available' => $platforms,
            'show_selector' => $platforms->count() > 1,
        ];
    }
}
