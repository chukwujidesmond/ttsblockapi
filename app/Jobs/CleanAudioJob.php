<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AudioCleanerService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\NoiseRemover;

class CleanAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    

    public int $timeout = 600;
    public int $tries   = 2;

    public function __construct(
        private string $jobId,
        private string $rawPath,
        private string $slug,
        private array  $options = []
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
                ->post(config('services.audio_cleaner.url') . '/clean', $this->options);

            if ($response->failed()) {
                throw new \RuntimeException($response->body());
            }

            $cleanedPath = "audio/cleaned/{$this->jobId}_clean.wav";
            // Storage::put($cleanedPath, $response->body());
             Storage::disk('s3')->put($cleanedPath, $response->body());
            $s3Url = Storage::disk('s3')->url($s3Path);

            // delete raw only after successful clean
            Storage::delete($this->rawPath);

              $updated = NoiseRemover::where('slug', $this->slug)
                ->update([
                    'media_name' => "{$this->jobId}.mp3",
                    'media_url'  => $s3Url,
                    'status'     => 'completed',
                ]);


            Cache::put("audio:{$this->jobId}", [
                'status'       => 'done',
                'download_url' => Storage::url($cleanedPath),
                'model_used'   => $response->header('X-Processing-Model'),
            ], now()->addDay());

           

        } catch (\Throwable $e) {
            Cache::put("audio:{$this->jobId}", [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ], now()->addHours(2));
        }
    }
}
