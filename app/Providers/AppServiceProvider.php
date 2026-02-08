<?php

namespace App\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;
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

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

            return (new MailMessage)
                ->subject('Redefinição de senha')
                ->view('emails.password-reset', [
                    'url' => $url,
                    'userName' => $notifiable->name ?? null,
                    'expireMinutes' => $expireMinutes,
                ]);
        });
    }
}
