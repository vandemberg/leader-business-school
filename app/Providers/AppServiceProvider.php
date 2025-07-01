<?php

namespace App\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use App\Events\VideoCreated;
use App\Listeners\HandleVideoCreated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\YouTubeService::class, function ($app) {
            return new \App\Services\YouTubeService();
        });

        $this->app->singleton(\App\Services\OpenAIService::class, function ($app) {
            return new \App\Services\OpenAIService();
        });

        $this->app->singleton(\App\Services\NewsletterService::class, function ($app) {
            return new \App\Services\NewsletterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
        VideoCreated::class,
        HandleVideoCreated::class,
    );
    }
}
