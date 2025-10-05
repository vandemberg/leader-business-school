<?php

namespace Database\Seeders;

use App\Models\Platform;
use App\Models\PlatformUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
  /**
   * Run the database seeder.
   */
  public function run(): void
  {
    // Criar algumas plataformas de exemplo
    $platforms = [
      [
        'name' => 'Plataforma Principal',
        'slug' => 'principal',
        'brand' => 'LBS'
      ],
      [
        'name' => 'Academia Corporativa',
        'slug' => 'corporativa',
        'brand' => 'Corp'
      ],
      [
        'name' => 'Universidade Online',
        'slug' => 'universidade',
        'brand' => 'Uni'
      ]
    ];

    foreach ($platforms as $platformData) {
      Platform::firstOrCreate(
        ['slug' => $platformData['slug']],
        $platformData
      );
    }

    // Associar usuários às plataformas (para teste)
    $users = User::all();
    $platforms = Platform::all();

    foreach ($users as $user) {
      // Associa cada usuário a pelo menos 2 plataformas para testar o seletor
      foreach ($platforms->take(2) as $platform) {
        PlatformUser::firstOrCreate([
          'user_id' => $user->id,
          'platform_id' => $platform->id
        ]);
      }

      // Define a primeira plataforma como atual se não tiver uma
      if (!$user->current_platform_id && $platforms->count() > 0) {
        $user->update(['current_platform_id' => $platforms->first()->id]);
      }
    }
  }
}
