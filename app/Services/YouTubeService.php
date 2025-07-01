<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class YouTubeService
{
    /**
     * Download audio from YouTube video
     *
     * @param string $videoUrl URL or video ID from YouTube
     * @return string|null Path to the downloaded audio file
     */
    public function downloadAudio(string $videoId): ?string
    {
        try {
            // Create storage directory if it doesn't exist
            $storagePath = storage_path('app/public/audio');

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $outputFile = $storagePath . '/' . $videoId . '.mp3';

            // Check if yt-dlp is installed
            $checkProcess = new Process(['which', 'yt-dlp']);
            $checkProcess->run();

            if (!$checkProcess->isSuccessful()) {
                Log::error('YouTube Service: yt-dlp not found. Please install it first.');
                return null;
            }

            // Use yt-dlp to download only audio in mp3 format
            $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);
            $process = new Process([
                'yt-dlp',
                '-x',
                '--audio-format',
                'mp3',
                '--audio-quality',
                '0',
                '-o',
                $outputFile,
                "https://www.youtube.com/watch?v={$videoId}"
            ]);

            $process->setTimeout(3600); // 1 hour timeout
            $process->run();

            // Check if download was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            Log::info("YouTube Service: Successfully downloaded audio for video ID: {$videoId}");
            return $outputFile;
        } catch (Exception $e) {
            Log::error("YouTube Service: Error downloading audio: " . $e->getMessage());
            return null;
        }
    }
}
