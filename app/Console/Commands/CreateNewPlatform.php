<?php

namespace App\Console\Commands;

use App\UseCases\Accounts\CreatePlatformUseCase;
use Illuminate\Console\Command;

class CreateNewPlatform extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-new-platform {name?} {slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new platform to add courses using admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Platform name');

        if (!$name) {
            $this->error('Platform name is required.');
            return;
        }

        $slug = $this->argument('slug') ?? $this->ask('Platform slug');

        if (!$slug) {
            $this->error('Platform slug is required.');
            return;
        }

        $createPlatform = new CreatePlatformUseCase();
        $platform = $createPlatform->execute(new \App\Models\Platform([
            'name' => $name,
            'slug' => $slug,
        ]));

        $this->info("Platform '{$platform->name}' created with slug '{$platform->slug}'.");
    }
}
