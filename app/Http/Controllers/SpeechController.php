<?php

namespace App\Http\Controllers;

use App\Services\GeminiTTSService;
use Aws\S3\S3Client;
use Aws\TranscribeService\TranscribeServiceClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\ProcessAudioMix;
use App\Jobs\ProcessAudioTranscription;
use App\Models\VoiceOVer;

class SpeechController  extends Controller
{
    // private S3Client $s3Client;
    // private TranscribeServiceClient $transcribeClient;

    // public function __construct(
    //     protected GeminiTTSService $ttsService,
    //     S3Client $s3Client,
    //     TranscribeServiceClient $transcribeClient
    // ) {
    //     $this->s3Client = $s3Client;
    //     $this->transcribeClient = $transcribeClient;
    //     $this->middleware('auth:sanctum');
    // }

    public function __construct(protected GeminiTTSService $ttsService)
    {
        $this->middleware('auth:sanctum');
    }

    public function createVoiceOver(Request $request){

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                // 'script' => 'nullable|string',
                // 'type' => 'required|string|in:short,medium,long',
            ]);

            $user = Auth::user();
            $voiceOver = new VoiceOVer();
            $voiceOver->user_id = $user->id;
            $voiceOver->title = $request->input('title');
            $voiceOver->slug = Str::random(30);
            $voiceOver->type = 'voice_over';
            $voiceOver->status = 'draft';
            $voiceOver->save();

            return response()->json([
                'success' => true,
                'voice_over' => $voiceOver
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
       
    }

    public function getVoiceOver($slug){
        try {
            if (!$slug) {
                return response()->json(['success' => false, 'error' => 'Slug is required'], 400);
            }
            $voiceOver = VoiceOVer::where('slug', $slug)->firstOrFail();
           
            return response()->json([
                'success' => true,
                'voice_over' => $voiceOver
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'Voice over not found'], 404);
        }
    }
    /**
     * Generate audio and download it
     */
    public function generateAudio(Request $request)
    {
        $request->validate([
            'text'         => 'required|string|max:5000',
            'prompt'       => 'nullable|string|max:300',
            'voice'        => 'nullable|string',
            'language'     => 'nullable|string',
            'encoding'     => 'nullable|in:LINEAR16,MP3,OGG_OPUS',
            'pitch'        => 'nullable|numeric|between:-20,20',
            'speaking_rate'=> 'nullable|numeric|between:0.25,4',
        ]);

        try {
            $path = $this->ttsService->generate(
                text:        $request->input('text'),
                prompt:      $request->input('prompt', 'Read aloud in a clear and natural tone.'),
                voiceName:   $request->input('voice', 'Achernar'),
                languageCode:$request->input('language', 'en-us'),
                encoding:    $request->input('encoding', 'MP3'),  // changed default
                pitch:       (float) $request->input('pitch', 0),
                speakingRate:(float) $request->input('speaking_rate', 1),
            );

            $filename = 'speech_' . now()->format('Ymd_His') . '.mp3';

            return response()->download($path, $filename, [
                'Content-Type' => 'audio/mpeg',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Return audio as base64 JSON (useful for AJAX / frontend players)
     */
    public function generateAudioJson(Request $request)
    {
        $request->validate([
            'text'   => 'required|string|max:5000',
            'prompt' => 'nullable|string|max:300',
            'voice'  => 'nullable|string',
        ]);

        try {
            $base64 = $this->ttsService->generateBase64(
                text:      $request->input('text'),
                prompt:    $request->input('prompt', 'Read aloud in a clear and natural tone.'),
                voiceName: $request->input('voice', 'Achernar'),
            );

            return response()->json([
                'success'  => true,
                'audio'    => $base64,
                'mimeType' => 'audio/mp3',
            ]);
            // return response()->json([
            //     'success'  => true,
            //     'audio'    => $base64,
            //     'mimeType' => 'audio/mpeg',  // not audio/wav
            // ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function listVoice()
    {
        return response()->json([
            'success' => true,
            'voices'  => config('gemini_voices.voices'),
            'models'  => config('gemini_voices.models'),
        ]);
    }

    public function listVoiceOver()
    {
        $user = Auth::user();
        $voiceOvers = VoiceOVer::where('user_id', $user->id)->where('type', 'voice_over')->get();

        return response()->json([
            'success' => true,
            'voice_overs' => $voiceOvers
        ]);
    }

    // public function processAudioTranscription(Request $request)
    // {
    //     try {

    //         if (!$request->hasFile('audio_file')) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Audio not found'
    //             ], 400);
    //         }

    //         $file = $request->file('audio_file');

    //         $validExtensions = ['mp3','wav','mpeg'];

    //         if (!in_array(strtolower($file->getClientOriginalExtension()), $validExtensions)) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Unsupported audio format'
    //             ]);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Save file locally
    //         |--------------------------------------------------------------------------
    //         */

    //         $fileName = time().'_'.$file->getClientOriginalName();
    //         $destinationPath = storage_path('app/audios');

    //         if (!file_exists($destinationPath)) {
    //             mkdir($destinationPath,0755,true);
    //         }

    //         $file->move($destinationPath,$fileName);

    //         $localPath = $destinationPath.'/'.$fileName;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Upload to S3
    //         |--------------------------------------------------------------------------
    //         */

    //         $bucket = env('AWS_BUCKET');
    //         $region = env('AWS_DEFAULT_REGION');

    //         $s3 = new S3Client([
    //             'version' => 'latest',
    //             'region' => $region,
    //             'credentials' => [
    //                 'key' => env('AWS_ACCESS_KEY_ID'),
    //                 'secret' => env('AWS_SECRET_ACCESS_KEY')
    //             ]
    //         ]);

    //         $key = 'transcriptions/'.$fileName;

    //         $s3->putObject([
    //             'Bucket' => $bucket,
    //             'Key' => $key,
    //             'Body' => fopen($localPath,'r'),
    //             'ACL' => 'private'
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Start Transcription
    //         |--------------------------------------------------------------------------
    //         */

    //         $transcribe = new TranscribeServiceClient([
    //             'version' => 'latest',
    //             'region' => $region,
    //             'credentials' => [
    //                 'key' => env('AWS_ACCESS_KEY_ID'),
    //                 'secret' => env('AWS_SECRET_ACCESS_KEY')
    //             ]
    //         ]);

    //         $jobName = 'transcription_'.uniqid();

    //         $transcribe->startTranscriptionJob([
    //             'TranscriptionJobName' => $jobName,
    //             'LanguageCode' => 'en-US',
    //             'Media' => [
    //                 'MediaFileUri' => "s3://{$bucket}/{$key}"
    //             ]
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Wait for transcription result
    //         |--------------------------------------------------------------------------
    //         */

    //         $attempt = 0;
    //         $maxAttempts = 60;

    //         do {

    //             sleep(5);

    //             $result = $transcribe->getTranscriptionJob([
    //                 'TranscriptionJobName' => $jobName
    //             ]);

    //             $status = $result['TranscriptionJob']['TranscriptionJobStatus'];

    //             if ($status == 'COMPLETED') {
    //                 break;
    //             }

    //             if ($status == 'FAILED') {
    //                 throw new \Exception('Transcription failed');
    //             }

    //             $attempt++;

    //         } while ($attempt < $maxAttempts);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Fetch transcript JSON
    //         |--------------------------------------------------------------------------
    //         */

    //         $transcriptUrl = $result['TranscriptionJob']['Transcript']['TranscriptFileUri'];

    //         $client = new Client();
    //         $response = $client->get($transcriptUrl);

    //         $data = json_decode($response->getBody()->getContents(),true);

    //         $text = $data['results']['transcripts'][0]['transcript'] ?? '';

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Save in database
    //         |--------------------------------------------------------------------------
    //         */

    //         // $transcription = new AudioTranscriptionModel();
    //         // $transcription->user_id = Auth::id();
    //         // $transcription->title = 'Audio Transcription';
    //         // $transcription->transcription = $text;
    //         // $transcription->slug = Str::random(30);
    //         // $transcription->save();
           
    //         /*
    //         |--------------------------------------------------------------------------
    //         | Cleanup S3 file
    //         |--------------------------------------------------------------------------
    //         */

    //         $s3->deleteObject([
    //             'Bucket' => $bucket,
    //             'Key' => $key
    //         ]);

    //         return response()->json([
    //             'status' => 'success',
    //             'text' => $text,
    //             // 'url' => route('user.audio.to.text.editor',$transcription->slug)
    //         ]);

    //     } catch (\Exception $e) {

    //         Log::error($e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Transcription failed'
    //         ],500);
    //     }
    // }

    public function processAudioTranscription(Request $request)
    {
        if (!$request->hasFile('audio_file')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Audio not found'
            ], 400);
        }

        $file            = $request->file('audio_file');
        $validExtensions = ['mp3', 'wav', 'mpeg'];

        if (!in_array(strtolower($file->getClientOriginalExtension()), $validExtensions)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unsupported audio format'
            ], 422);
        }

        // Save file locally before dispatching
        $fileName        = time() . '_' . $file->getClientOriginalName();
        $destinationPath = storage_path('app/audios');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $fileName);

        $jobId = (string) Str::uuid();

        cache()->put(
            "transcription_job:{$jobId}",
            ['status' => 'queued'],
            now()->addHours(2)
        );

        $user = Auth::user();
        $voiceOver = new VoiceOVer();
        $voiceOver->user_id = $user->id;
        $voiceOver->title = $request->input('title');
        $voiceOver->slug = Str::random(30);
        $voiceOver->type = 'transcription';
        $voiceOver->status = 'processing';
        $voiceOver->save();

        ProcessAudioTranscription::dispatch($fileName, $jobId, $voiceOver->slug)
            ->onQueue('transcription');

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobId,
        ], 202);
    }

    public function transcriptionStatus(string $jobId)
    {
        $result = cache()->get("transcription_job:{$jobId}");

        if (!$result) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($result);
    }

     public function listTranscription()
    {
        $user = Auth::user();
        $transcriptions = VoiceOVer::where('user_id', $user->id)->where('type', 'transcription')->get();

        return response()->json([
            'success' => true,
            'transcription' => $transcriptions
        ]);
    }

    public function processAudioConvert(Request $request)
    {
        $request->validate([
            'slug'                     => 'required|string',
            'tracks'                   => 'required|array|min:1',
            'tracks.*.audio'           => 'required|string',
            'tracks.*.volume'          => 'required|numeric|min:0|max:5',
            'tracks.*.audio_name'      => 'required|string',
            'tracks.*.position.start'  => 'required|numeric|min:0',
            'tracks.*.position.end'    => 'required|numeric|gt:tracks.*.position.start',
        ]);

        $jobId = (string) Str::uuid();

        // Mark as queued immediately so the client can start polling
        cache()->put(
            "audio_job:{$jobId}",
            ['status' => 'queued'],
            now()->addHours(2)
        );

        
         VoiceOver::where('slug', $request->slug)->update(['status' => 'processing']);

        ProcessAudioMix::dispatch($request->input('tracks'), $jobId, $request->slug)
            ->onQueue('audio');

        return response()->json([
            'status' => 'queued',
            'job_id' => $jobId,
        ], 202);
    }

    /**
     * Poll endpoint — returns status/url once done.
     */
    public function jobStatus(string $jobId)
    {
        $result = cache()->get("audio_job:{$jobId}");

        if (!$result) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($result);
    }

    function deleteVoiceOver($slug){
        try {
            $voiceOver = VoiceOVer::where('slug', $slug)->firstOrFail();
            if ($voiceOver->media_url) {
                // If there's an associated media file, you might want to delete it from S3 here
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => env('AWS_DEFAULT_REGION'),
                    'credentials' => [
                        'key' => env('AWS_ACCESS_KEY_ID'),
                        'secret' => env('AWS_SECRET_ACCESS_KEY')
                    ]
                ]);

                $s3Key = parse_url($voiceOver->media_url, PHP_URL_PATH);
                $s3Key = ltrim($s3Key, '/'); // Remove leading slash

                try {
                    $s3->deleteObject([
                        'Bucket' => env('AWS_BUCKET'),
                        'Key' => $s3Key
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to delete S3 object: {$voiceOver->media_url}", ['error' => $e->getMessage()]);
                    // Continue with deletion even if S3 cleanup fails
                }
            }
            $voiceOver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Voice over deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'Voice over not found'], 404);
        }
    }

    public function downloadAudio($slug){
        try {
            $voiceOver = VoiceOVer::where('slug', $slug)->firstOrFail();

            if (!$voiceOver->media_url) {
                return response()->json(['success' => false, 'error' => 'No audio available for this voice over'], 404);
            }

            // Generate a temporary signed URL for the S3 object
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY')
                ]
            ]);

            $s3Key = parse_url($voiceOver->media_url, PHP_URL_PATH);
            $s3Key = ltrim($s3Key, '/'); // Remove leading slash

            $command = $s3->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $s3Key
            ]);

            $request = $s3->createPresignedRequest($command, '+20 minutes');

            $presignedUrl = (string) $request->getUri();

            return response()->json([
                'success' => true,
                'download_url' => $presignedUrl
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'Voice over not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

}
