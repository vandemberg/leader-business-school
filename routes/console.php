<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Mail\SendEmail;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('send-mail', function () {
    $apiKey = config('services.sendgrid.api_key');

    if (!$apiKey) {
        $this->error('SENDGRID_API_KEY não configurado no .env');
        return 1;
    }

    $toEmail = $this->ask('Digite o email do destinatário');
    if (!$toEmail) {
        $this->error('Email do destinatário é obrigatório');
        return 1;
    }

    try {
        $sendEmail = new SendEmail();

        $result = $sendEmail->send(
            to: $toEmail,
            subject: 'Test Email from SendGrid API',
            html: '<h1>Test Email</h1><p>This is a test email sent using the SendGrid API.</p>',
            text: 'This is a test email sent using the SendGrid API.'
        );

        if ($result) {
            $this->info('Email enviado com sucesso!');
        } else {
            $this->error('Falha ao enviar email');
            return 1;
        }

        return 0;
    } catch (\Exception $e) {
        $this->error('Erro ao enviar email: ' . $e->getMessage());
        return 1;
    }
})->purpose('Send test email using SendGrid API');

Artisan::command('send-mail-template', function () {
    try {
        $sendEmail = new SendEmail();

        $toEmail = $this->ask('Digite o email do destinatário');
        if (!$toEmail) {
            $this->error('Email do destinatário é obrigatório');
            return 1;
        }

        $subject = $this->ask('Digite o assunto do email', 'Teste de Template');
        $view = $this->ask('Digite o nome da view (ex: emails.invitation)', 'emails.invitation');

        // Dados de exemplo - podem ser customizados conforme necessário
        $data = [
            'invitation' => (object) [
                'token' => 'test-token-' . uniqid(),
                'expires_at' => now()->addDays(7),
            ],
            'isNewUser' => true,
            'inviteUrl' => url('/invite/register/test-token-' . uniqid()),
            'platform' => (object) [
                'name' => config('app.name', 'Leader Business School'),
            ],
        ];

        $result = $sendEmail->sendTemplate(
            to: $toEmail,
            subject: $subject,
            view: $view,
            data: $data
        );

        if ($result) {
            $this->info('Email com template enviado com sucesso!');
        } else {
            $this->error('Falha ao enviar email');
            return 1;
        }

        return 0;
    } catch (\Exception $e) {
        $this->error('Erro ao enviar email: ' . $e->getMessage());
        return 1;
    }
})->purpose('Send email using template via SendGrid API');
