<?php

namespace App\Services;

use Exception;
use OpenAI;
use OpenAI\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class OpenAIService
{
    private Client $client;

    /**
     * Create a new OpenAI service instance
     */
    public function __construct()
    {
        $apiKey = Config::get('services.openai.api_key');
        if (empty($apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }

        $this->client = OpenAI::client($apiKey);
    }

    /**
     * Transcribe audio file using OpenAI's Whisper API
     *
     * @param string $audioFilePath Path to the audio file
     * @return string|null Transcription text or null on error
     */
    public function transcribeAudio(string $audioFilePath): ?string
    {
        try {
            if (!file_exists($audioFilePath)) {
                Log::error("OpenAI Service: Audio file not found: {$audioFilePath}");
                return null;
            }

            $response = $this->client->audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($audioFilePath, 'r'),
                'response_format' => 'text',
            ]);

            Log::info("OpenAI Service: Successfully transcribed audio file");
            return $response->text;

        } catch (Exception $e) {
            Log::error("OpenAI Service: Error çƒtranscribing audio: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate a newsletter based on transcription text using ChatGPT
     *
     * @param string $transcription The transcription text
     * @param string $videoTitle The title of the video
     * @return string|null Generated newsletter content or null on error
     */
    public function generateNewsletter(string $transcription, string $videoTitle): ?string
    {
        try {
            $prompt = "Por favor, crie uma newsletter bem formatada e profissional baseada na transcrição abaixo. ";
            $prompt .= "A newsletter deve resumir os principais pontos da transcrição, destacar as informações mais importantes ";
            $prompt .= "e apresentar o conteúdo de forma organizada e atraente para os leitores. ";
            $prompt .= "Use o seguinte título como referência: \"{$videoTitle}\". ";
            $prompt .= "TRANSCRIÇÃO: \n\n{$transcription}";

            $response = $this->client->chat()->create([
                'model' => 'gpt-4.1-nano',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um assistente especializado em criar newsletters profissionais a partir de transcrições de vídeos.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000
            ]);

            Log::info("OpenAI Service: Successfully generated newsletter from transcription");
            return $response->choices[0]->message->content;

        } catch (Exception $e) {
            Log::error("OpenAI Service: Error generating newsletter: " . $e->getMessage());
            return null;
        }
    }
}
