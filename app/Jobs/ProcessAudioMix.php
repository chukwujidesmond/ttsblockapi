<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\VoiceOVer;

class ProcessAudioMix implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;
    public int $backoff = 10;

    public function __construct(
        protected array  $tracks,
        protected string $jobId,
        protected string $slug
    ) {}

    public function handle(): void
    {
        // Only input temp files go here — output is cleaned up explicitly after upload
        $inputTempFiles = [];
        $localOutput    = null;

        Log::info('ProcessAudioMix started', [
            'jobId' => $this->jobId,
            'slug'  => $this->slug,
        ]);

        try {
            $inputs    = [];
            $filters   = [];
            $mixInputs = [];

            // ── 1. Write each track to a temp file ──────────────────────────
            foreach ($this->tracks as $index => $track) {
                $raw     = preg_replace('#^data:audio/\w+;base64,#i', '', $track['audio']);
                $decoded = base64_decode($raw, strict: true);

                if ($decoded === false) {
                    throw new \RuntimeException("Invalid base64 data for track {$index}");
                }

                $tmpPath = storage_path("app/tmp/audio_{$this->jobId}_{$index}.mp3");

                if (!is_dir(dirname($tmpPath))) {
                    mkdir(dirname($tmpPath), 0755, true);
                }

                file_put_contents($tmpPath, $decoded);

                // Track input files for cleanup in finally
                $inputTempFiles[] = $tmpPath;

                Log::info("Track {$index} written to disk", ['path' => $tmpPath, 'bytes' => strlen($decoded)]);

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

            // ── 2. Build ffmpeg filter_complex ───────────────────────────────
            $trackCount    = count($this->tracks);
            $filterComplex = implode('; ', $filters)
                           . '; '
                           . implode('', $mixInputs)
                           . "amix=inputs={$trackCount}:duration=longest:normalize=0";

            // ── 3. Prepare output path (NOT added to cleanup list yet) ───────
            $outputDir = storage_path('app/tmp');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $localOutput = "{$outputDir}/{$this->jobId}.mp3";

            // ── 4. Run ffmpeg ─────────────────────────────────────────────────
            $command = 'ffmpeg '
                     . implode(' ', $inputs)
                     . ' -filter_complex ' . escapeshellarg($filterComplex)
                     . ' -c:a libmp3lame -q:a 2'
                     . ' -y ' . escapeshellarg($localOutput)
                     . ' 2>&1';

            Log::info('Running ffmpeg', ['command' => $command]);

            exec($command, $output, $exitCode);

            if ($exitCode !== 0) {
                throw new \RuntimeException(
                    "ffmpeg failed (exit {$exitCode}): " . implode("\n", $output)
                );
            }

            if (!file_exists($localOutput) || filesize($localOutput) === 0) {
                throw new \RuntimeException("ffmpeg produced no output file at {$localOutput}");
            }

            Log::info('ffmpeg succeeded', [
                'output' => $localOutput,
                'size'   => filesize($localOutput),
            ]);

            // ── 5. Upload to S3 ───────────────────────────────────────────────
            $s3Path = "voiceovers/{$this->jobId}.mp3";

            Storage::disk('s3')->put(
                $s3Path,
                file_get_contents($localOutput),
                'private'
            );

            // ── 6. Clean up local output AFTER successful upload ──────────────
            @unlink($localOutput);
            $localOutput = null; // prevent double-delete in finally

            $s3Url = Storage::disk('s3')->url($s3Path);

            Log::info('Audio uploaded to S3', ['url' => $s3Url]);

            // ── 7. Update VoiceOver record ────────────────────────────────────
            // $updated = VoiceOver::where('slug', $this->slug)
            //     ->update([
            //         'audio_path' => $s3Path,
            //         'audio_url'  => $s3Url,
            //         'status'     => 'published',
            //     ]);

            // if (!$updated) {
            //     throw new \RuntimeException("VoiceOver record not found for slug: {$this->slug}");
            // }

            // ── 8. Cache the result for polling ──────────────────────────────
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
            Log::error('ProcessAudioMix failed', [
                'jobId' => $this->jobId,
                'slug'  => $this->slug,
                'error' => $e->getMessage(),
            ]);

            VoiceOver::where('slug', $this->slug)
                ->update(['status' => 'failed']);

            cache()->put(
                "audio_job:{$this->jobId}",
                ['status' => 'failed', 'error' => $e->getMessage()],
                now()->addHours(1)
            );

            throw $e;

        } finally {
            // Clean up input temp files
            foreach ($inputTempFiles as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }

            // Clean up output file only if it wasn't already deleted after upload
            // (i.e. if an exception occurred before the explicit unlink above)
            if ($localOutput && file_exists($localOutput)) {
                @unlink($localOutput);
            }
        }
    }
}