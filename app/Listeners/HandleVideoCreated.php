<?php

namespace App\Listeners;

use App\Events\VideoCreated;
use App\Services\YouTubeService;
use App\Services\OpenAIService;
use App\Services\NewsletterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleVideoCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VideoCreated $event): void
    {
        // Obtém a instância do vídeo do evento
        $video = $event->video;

        // Registrar no log que um vídeo foi criado
        Log::info("Processando novo vídeo: {$video->title} (URL: {$video->url})");

        try {
            // 1. Download do áudio do vídeo do YouTube
            $youtubeService = new YouTubeService();
            $audioFilePath = $youtubeService->downloadAudio($video->url);

            if (!$audioFilePath) {
                Log::error("Falha ao baixar o áudio do vídeo: {$video->title}");
                return;
            }

            Log::info("Áudio do vídeo baixado com sucesso: {$audioFilePath}");

            // 2. Transcrever o áudio usando a API do OpenAI
            $openAIService = new OpenAIService();
            $transcription = $openAIService->transcribeAudio($audioFilePath);

            if (!$transcription) {
                Log::error("Falha ao transcrever o áudio do vídeo: {$video->title}");
                return;
            }

            // Salvar a transcrição no modelo de vídeo
            $video->transcription = $transcription;
            $video->save();

            Log::info("Transcrição do vídeo concluída com sucesso: {$video->id}");

            // 3. Gerar uma newsletter baseada na transcrição
            $newsletter = $openAIService->generateNewsletter($transcription, $video->title);

            if (!$newsletter) {
                Log::error("Falha ao gerar a newsletter para o vídeo: {$video->title}");
                return;
            }

            Log::info("Newsletter gerada com sucesso para o vídeo: {$video->id}");

            // 4. Salvar e enviar a newsletter
            $newsletterService = new NewsletterService();
            $newsletterService->saveNewsletter($video, $newsletter);

            // Opcionalmente, enviar a newsletter (comentado por enquanto)
            $newsletterService->sendNewsletter($video, $newsletter);

            Log::info("Processamento completo do vídeo: {$video->title}");

        } catch (\Exception $e) {
            Log::error("Erro ao processar o vídeo {$video->title}: " . $e->getMessage());
        }
    }
}
