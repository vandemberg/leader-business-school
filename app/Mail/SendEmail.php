<?php

namespace App\Mail;

use SendGrid\Mail\Mail;
use SendGrid;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class SendEmail
{
    protected SendGrid $client;
    protected string $apiKey;
    protected string $fromEmail;
    protected string $fromName;

    public function __construct()
    {
        $this->apiKey = config('services.sendgrid.api_key') ?? getenv('SENDGRID_API_KEY');

        if (!$this->apiKey) {
            throw new Exception('SendGrid API key não configurado. Defina SENDGRID_API_KEY no .env');
        }

        $this->client = new SendGrid($this->apiKey);
        $this->fromEmail = 'no-reply@leadersincompany.com';
        $this->fromName = 'LBS';
    }

    /**
     * Envia um e-mail usando template Blade
     *
     * @param string $to Email do destinatário
     * @param string $toName Nome do destinatário (opcional)
     * @param string $subject Assunto do e-mail
     * @param string $view Nome da view Blade
     * @param array $data Dados para a view
     * @param string|null $fromEmail Email do remetente (opcional)
     * @param string|null $fromName Nome do remetente (opcional)
     * @return bool
     * @throws Exception
     */
    public function sendTemplate(
        string $to,
        string $subject,
        string $view,
        array $data = [],
        ?string $toName = null,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        try {
            // Renderizar o template Blade
            $html = View::make($view, $data)->render();

            // Criar versão texto simples
            $text = strip_tags($html);

            return $this->send(
                to: $to,
                toName: $toName,
                subject: $subject,
                html: $html,
                text: $text,
                fromEmail: $fromEmail,
                fromName: $fromName
            );
        } catch (Exception $e) {
            Log::error('Erro ao enviar email via SendGrid', [
                'to' => $to,
                'subject' => $subject,
                'view' => $view,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Envia um e-mail simples (HTML e texto)
     *
     * @param string $to Email do destinatário
     * @param string $subject Assunto do e-mail
     * @param string $html Conteúdo HTML
     * @param string|null $text Conteúdo texto (opcional)
     * @param string|null $toName Nome do destinatário (opcional)
     * @param string|null $fromEmail Email do remetente (opcional)
     * @param string|null $fromName Nome do remetente (opcional)
     * @return bool
     * @throws Exception
     */
    public function send(
        string $to,
        string $subject,
        string $html,
        ?string $text = null,
        ?string $toName = null,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        try {
            $email = new Mail();

            // Configurar remetente
            $email->setFrom(
                $fromEmail ?? $this->fromEmail,
                $fromName ?? $this->fromName
            );

            // Configurar destinatário
            $email->addTo($to, $toName);

            // Configurar assunto
            $email->setSubject($subject);

            // Configurar conteúdo HTML
            $email->addContent("text/html", $html);

            // Configurar conteúdo texto
            if ($text) {
                $email->addContent("text/plain", $text);
            } else {
                $email->addContent("text/plain", strip_tags($html));
            }

            // Enviar e-mail
            $response = $this->client->send($email);

            $statusCode = $response->statusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Email enviado via SendGrid', [
                    'to' => $to,
                    'subject' => $subject,
                    'status_code' => $statusCode,
                ]);

                return true;
            } else {
                $errorMessage = 'SendGrid API retornou erro: ' . $response->body();
                Log::error('Erro ao enviar email via SendGrid', [
                    'to' => $to,
                    'subject' => $subject,
                    'status_code' => $statusCode,
                    'response_body' => $response->body(),
                ]);

                throw new Exception($errorMessage);
            }
        } catch (Exception $e) {
            Log::error('Erro ao enviar email via SendGrid', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
