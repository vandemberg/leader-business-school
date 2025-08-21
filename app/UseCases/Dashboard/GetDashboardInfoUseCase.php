<?php

namespace App\UseCases\Dashboard;

use App\Repositories\PlatformRepository;
use App\UseCases\IUseCase;

class GetDashboardInfoUseCase implements IUseCase
{
    private PlatformRepository $platformRepository;

    public function __construct()
    {
        $this->platformRepository = new PlatformRepository();
    }

    public function execute(mixed $input = null): mixed
    {
        $platformId = $input['platform_id'] ?? null;

        if (!$platformId) {
            throw new \InvalidArgumentException('Platform ID is required');
        }

        $mostAccessedCourses = $this->platformRepository->mostAccessedCourses($platformId);

        return [
            'courses' => $mostAccessedCourses,
            'platform' => $platformId,
        ];
    }
}
