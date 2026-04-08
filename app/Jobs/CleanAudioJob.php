<?php

namespace App\Jobs;

use App\Models\NoiseRemover;
use App\Services\AudioCleanerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public int $timeout = 600;

    public int $tries = 2;

    public function __construct(
        private string $jobId,
        private string $rawPath,
        private string $slug,
        private array $options = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AudioCleanerService $cleaner): void
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(300)
                ->withHeaders(['X-Api-Secret' => config('services.audio_cleaner.secret')])
                ->attach('file', Storage::get($this->rawPath), basename($this->rawPath))
                ->post(config('services.audio_cleaner.url').'/clean', $this->options);

            if ($response->failed()) {
                throw new \RuntimeException($response->body());
            }

            $cleanedPath = "audio/cleaned/{$this->jobId}_clean.wav";
            Storage::put($cleanedPath, $response->body());
            Storage::disk('s3')->put($cleanedPath, $response->body(), 'private');

            $s3Url = Storage::disk('s3')->url($cleanedPath);

            Log::info('Audio Cleaned to S3', ['url' => $s3Url]);

            // delete raw only after successful clean
            Storage::delete($this->rawPath);

            $updated = NoiseRemover::where('slug', $this->slug)
                ->update([
                    'media_name' => "{$this->jobId}.mp3",
                    'media_url' => $s3Url,
                    'status' => 'completed',
                ]);

            // Cache::put("audio:{$this->jobId}", [
            //     'status' => 'done',
            //     'download_url' => $s3Url,
            //     'model_used' => $response->header('X-Processing-Model'),
            // ], now()->addDay());

        } catch (\Throwable $e) {
            Log::error('Audio clean failed', ['jobId' => $this->jobId, 'error' => $e->getMessage()]);

            NoiseRemover::where('slug', $this->slug)
                ->update([
                    'media_name' => "{$this->jobId}.mp3",
                    'status' => 'failed',
                ]);
        }
    }
}
