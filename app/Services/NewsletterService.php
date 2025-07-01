<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class NewsletterService
{
    /**
     * Save newsletter content to database or storage
     *
     * @param Video $video Video model
     * @param string $newsletterContent Generated newsletter content
     * @return bool Success state
     */
    public function saveNewsletter(Video $video, string $newsletterContent): bool
    {
        try {
            // Store newsletter content in a file
            $fileName = 'newsletter_' . $video->id . '_' . time() . '.html';
            $filePath = storage_path('app/public/newsletters/' . $fileName);

            // Create directory if it doesn't exist
            $dir = storage_path('app/public/newsletters');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Save the newsletter content to the file
            file_put_contents($filePath, $newsletterContent);

            // Update video record with newsletter path
            DB::table('videos')
                ->where('id', $video->id)
                ->update([
                    'newsletter_path' => 'newsletters/' . $fileName,
                    'updated_at' => now()
                ]);

            Log::info("Newsletter Service: Successfully saved newsletter for video ID: {$video->id}");
            return true;

        } catch (Exception $e) {
            dd($e);
            Log::error("Newsletter Service: Error saving newsletter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send newsletter to subscribers or specific users
     *
     * @param Video $video Video model
     * @param string $newsletterContent Generated newsletter content
     * @return bool Success state
     */
    public function sendNewsletter(Video $video, string $newsletterContent): bool
    {
        try {
            // In a real application, you would fetch subscribers from a database
            // For now, we'll just log that we would send the newsletter
            Log::info("Newsletter Service: Sending newsletter for video: {$video->title}");

            // Example code to send email (commented out for now)

            User::all()->each(function ($user) use ($video, $newsletterContent) {
                Mail::send([], [], function ($message) use ($user, $video, $newsletterContent) {
                    $message->to($user->email)
                        ->subject($video->title)
                        ->setBody($newsletterContent, 'text/html');
                });
            });

            return true;

        } catch (Exception $e) {
            Log::error("Newsletter Service: Error sending newsletter: " . $e->getMessage());
            return false;
        }
    }
}
