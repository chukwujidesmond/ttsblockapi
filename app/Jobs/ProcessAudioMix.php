<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\VoiceOver;

class ProcessAudioMix implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 10;

    public function __construct(
        protected array $tracks,
        protected string $jobId,
        protected string $slug
    ) {}

    public function handle(): void
    {
        $tempFiles = [];

        try {
            $inputs    = [];
            $filters   = [];
            $mixInputs = [];

            foreach ($this->tracks as $index => $track) {
                $raw     = preg_replace('#^data:audio/\w+;base64,#i', '', $track['audio']);
                $decoded = base64_decode($raw, strict: true);

                if ($decoded === false) {
                    throw new \RuntimeException("Invalid base64 for track {$index}");
                }

                $tmpPath     = storage_path("app/tmp/audio_{$this->jobId}_{$index}.mp3");
                $tempFiles[] = $tmpPath;

                if (!is_dir(dirname($tmpPath))) {
                    mkdir(dirname($tmpPath), 0755, true);
                }

                file_put_contents($tmpPath, $decoded);

                $start    = (float) $track['position']['start'];
                $end      = (float) $track['position']['end'];
                $volume   = (float) ($track['volume'] ?? 1.0);
                $duration = $end - $start;
                $delay    = (int) ($start * 1000);

                $inputs[]    = '-i ' . escapeshellarg($tmpPath);
                $filters[]   = "[{$index}:a]atrim=0:{$duration},asetpts=PTS-STARTPTS,"
                             . "volume={$volume},adelay={$delay}|{$delay}[a{$index}]";
                $mixInputs[] = "[a{$index}]";
            }

            $trackCount    = count($this->tracks);
            $filterComplex = implode('; ', $filters)
                           . '; '
                           . implode('', $mixInputs)
                           . "amix=inputs={$trackCount}:duration=longest:normalize=0";

            $outputDir = storage_path('app/tmp');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $localOutput = "{$outputDir}/{$this->jobId}.mp3";
            $tempFiles[] = $localOutput; // clean up after upload

            $command = 'ffmpeg '
                     . implode(' ', $inputs)
                     . ' -filter_complex ' . escapeshellarg($filterComplex)
                     . ' -c:a libmp3lame -q:a 2'
                     . ' -y ' . escapeshellarg($localOutput)
                     . ' 2>&1';

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \RuntimeException(
                    "ffmpeg failed (exit {$exitCode}): " . implode("\n", $output)
                );
            }

            // Upload to S3
            $s3Path = "voiceovers/{$this->slug}/{$this->jobId}.mp3";

            Storage::disk('s3')->put(
                $s3Path,
                file_get_contents($localOutput),
                'private' // or 'public' depending on your access needs
            );

            $s3Url = Storage::disk('s3')->url($s3Path);

             Log::error("VoiceOver record not found for slug: {$s3Url}");

            // Update the VoiceOver record via slug
            // $updated = VoiceOver::where('slug', $this->slug)
            //     ->update([
            //         'audio_path' => $s3Path,
            //         'audio_url'  => $s3Url,
            //         'status'     => 'published',
            //     ]);

            // if (!$updated) {
            //     throw new \RuntimeException("VoiceOver record not found for slug: {$this->slug}");
            // }


            cache()->put(
                "audio_job:{$this->jobId}",
                [
                    'status' => 'done',
                    'url'    => $s3Url,
                    'slug'   => $this->slug,
                ],
                now()->addHours(2)
            );

        } catch (\Throwable $e) {
            Log::error("ProcessAudioMix failed [{$this->jobId}] slug={$this->slug}: " . $e->getMessage());

            // Mark the record as failed so the UI can reflect it
            VoiceOver::where('slug', $this->slug)
                ->update(['status' => 'failed']);

            cache()->put(
                "audio_job:{$this->jobId}",
                ['status' => 'failed', 'error' => $e->getMessage()],
                now()->addHours(1)
            );

            throw $e;

        } finally {
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }
}