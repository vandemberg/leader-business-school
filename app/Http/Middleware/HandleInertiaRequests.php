<?php

namespace App\Http\Middleware;

use App\Services\StreakService;
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
        $user = $request->user();
        $streakInfo = null;

        if ($user) {
            $streakService = new StreakService();
            $streakInfo = $streakService->getStreakInfo($user);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'platform' => fn() => $this->getPlatformData($request),
            'streak' => $streakInfo,
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

        // Get all platforms for the user, ensuring no duplicates
        // Use unique() on collection instead of groupBy to avoid SQL issues with pivot table
        $platforms = $user->platforms()
            ->select('platforms.id', 'platforms.name', 'platforms.slug', 'platforms.brand')
            ->orderBy('platforms.name')
            ->get()
            ->unique('id')
            ->values();

        return [
            'current' => current_platform(),
            'available' => $platforms,
            'show_selector' => $platforms->count() > 1,
        ];
    }
}
