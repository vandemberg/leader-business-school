<?php

namespace App\UseCases\Accounts;

use App\Models\Platform;
use App\Models\User;
use App\UseCases\IUseCase;
use ErrorException;
use \Illuminate\Support\Facades\DB;

class CreatePlatformUseCase implements IUseCase
{

    public function execute($input = null): Platform
    {
        if (empty($input)) {
            throw new \InvalidArgumentException('Input is required');
        }

        $platform = $input['platform'] ?? null;
        DB::beginTransaction();

        try {
            $this->registerPlatform($platform);
            $userIds = $this->getUsers();
            $this->attachUsersToPlatform($platform, $userIds);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException($e->getMessage());
        }

        DB::commit();
        return $platform;
    }

    private function registerPlatform(Platform $platform): Platform
    {
        $platform->save();
        return $platform;
    }

    private function getUsers(): array
    {
        return User::where('role', 'student')->pluck('id')->toArray();
    }

    private function attachUsersToPlatform(Platform $platform, array $userIds): void
    {
        $platform->users()->attach($userIds);
    }
}
