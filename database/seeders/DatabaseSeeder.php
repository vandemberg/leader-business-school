<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Course;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->firstUser();
    }

    private function firstUser(): void
    {
        User::factory()->create([
            'name' => 'Vandemberg Lima',
            'email' => 'vandemberg.silva.lima@gmail.com',
            'password' => bcrypt('secret')
        ]);
    }
}
