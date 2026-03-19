<?php

namespace App\Jobs;

use Aws\S3\S3Client;
use Aws\TranscribeService\TranscribeServiceClient;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\VoiceOVer;

class ProcessAudioTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 600; // transcription can take a while
    public int $backoff = 30;

    public function __construct(
        protected string $fileName,
        protected string $jobId,
        protected string $slug
    ) {}

    public function handle(): void
    {
        $localPath = storage_path("app/audios/{$this->fileName}");

        $bucket = config('services.aws.bucket');
        $region = config('services.aws.region');
        $key    = "transcriptions/{$this->fileName}";

        $credentials = [
            'key'    => config('services.aws.key'),
            'secret' => config('services.aws.secret'),
        ];

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload to S3
            |--------------------------------------------------------------------------
            */

            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => $credentials,
            ]);

            $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'Body'   => fopen($localPath, 'r'),
                'ACL'    => 'private',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Start Transcription Job
            |--------------------------------------------------------------------------
            */

            $transcribe = new TranscribeServiceClient([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => $credentials,
            ]);

            $jobName = 'transcription_' . $this->jobId;

            $transcribe->startTranscriptionJob([
                'TranscriptionJobName' => $jobName,
                'LanguageCode'         => 'en-US',
                'Media'                => [
                    'MediaFileUri' => "s3://{$bucket}/{$key}",
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Poll for Completion
            |--------------------------------------------------------------------------
            */

            $attempt     = 0;
            $maxAttempts = 60; // 60 x 5s = 5 minutes max

            do {
                sleep(5);

                $result = $transcribe->getTranscriptionJob([
                    'TranscriptionJobName' => $jobName,
                ]);

                $status = $result['TranscriptionJob']['TranscriptionJobStatus'];

                if ($status === 'COMPLETED') break;

                if ($status === 'FAILED') {
                    throw new \RuntimeException('AWS Transcription job failed');
                }

                $attempt++;

            } while ($attempt < $maxAttempts);

            if ($attempt >= $maxAttempts) {
                throw new \RuntimeException('Transcription timed out after max attempts');
            }

            /*
            |--------------------------------------------------------------------------
            | Fetch Transcript Text
            |--------------------------------------------------------------------------
            */

            $transcriptUrl = $result['TranscriptionJob']['Transcript']['TranscriptFileUri'];

            $response = (new Client())->get($transcriptUrl);
            $data     = json_decode($response->getBody()->getContents(), true);
            $text     = $data['results']['transcripts'][0]['transcript'] ?? '';

            /*
            |--------------------------------------------------------------------------
            | Save to Database
            |--------------------------------------------------------------------------
            */

            // $transcription = new AudioTranscription();
            // $transcription->user_id       = $this->userId; // pass via constructor if needed
            // $transcription->title         = 'Audio Transcription';
            // $transcription->transcription = $text;
            // $transcription->slug          = Str::random(30);
            // $transcription->save();

             $updated = VoiceOver::where('slug', $this->slug)
                ->update([
                    'script' => $text,
                    'status'     => 'completed',
                ]);

            if (!$updated) {
                throw new \RuntimeException("VoiceOver record not found for slug: {$this->slug}");
            }
            /*
            |--------------------------------------------------------------------------
            | Cleanup S3 + Local File
            |--------------------------------------------------------------------------
            */

            $s3->deleteObject(['Bucket' => $bucket, 'Key' => $key]);

            if (file_exists($localPath)) {
                @unlink($localPath);
            }

            /*
            |--------------------------------------------------------------------------
            | Update Cache
            |--------------------------------------------------------------------------
            */

            cache()->put(
                "transcription_job:{$this->jobId}",
                [
                    'status' => 'done',
                    'text'   => $text,
                    // 'slug' => $transcription->slug,
                ],
                now()->addHours(2)
            );

        } catch (\Throwable $e) {
            Log::error("ProcessAudioTranscription failed [{$this->jobId}]: " . $e->getMessage());

            // Cleanup local file on failure too
            if (file_exists($localPath)) {
                @unlink($localPath);
            }

            // Best effort S3 cleanup
            try {
                $s3->deleteObject(['Bucket' => $bucket, 'Key' => $key]);
            } catch (\Throwable) {}

            cache()->put(
                "transcription_job:{$this->jobId}",
                ['status' => 'failed', 'error' => $e->getMessage()],
                now()->addHours(1)
            );

            throw $e;
        }
    }
}