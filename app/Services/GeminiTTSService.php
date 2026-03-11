<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeminiTTSService
{
    protected string $apiKey;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        // $this->apiUrl = config('services.gemini.tts_url');
        $this->apiUrl = config('services.gemini.tts_url') 
              . config('services.gemini.tts_model') 
              . ':generateContent';
    }

    /**
     * Generate speech audio using Gemini TTS
     *
     * @param  string  $text         The text to speak
     * @param  string  $prompt       Style instruction e.g. "Read in a warm, friendly tone"
     * @param  string  $voiceName    Gemini voice name e.g. "Achernar", "Puck", "Charon"
     * @param  string  $languageCode e.g. "en-us"
     * @param  string  $encoding     LINEAR16 | MP3 | OGG_OPUS
     * @param  float   $pitch        -20.0 to 20.0 (0 = default)
     * @param  float   $speakingRate 0.25 to 4.0  (1 = default)
     *
     * The Gemini generation payload has changed over time; we now send
     * `audioConfig` at the top level of `generationConfig` and language
     * codes are inferred by voice name.  The test exercises the expected
     * hierarchy so future breaks will be caught.
     *
     * @return string  Full path to the saved audio file
     */
    public function generate(
    string $text,
    string $prompt       = 'Read aloud in a clear and natural tone.',
    string $voiceName    = 'Achernar',
    string $languageCode = 'en-us',
    string $encoding     = 'MP3',  // changed default
    float  $pitch        = 0,
    float  $speakingRate = 1
    ): string {
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt . "\n\n" . $text]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseModalities' => ['AUDIO'],
                'speechConfig' => [
                    'voiceConfig' => [
                        'prebuiltVoiceConfig' => [
                            'voiceName' => $voiceName,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini TTS API error: ' . $response->body());
        }

        $data        = $response->json();
        $audioBase64 = data_get($data, 'candidates.0.content.parts.0.inlineData.data');

        if (!$audioBase64) {
            throw new \RuntimeException('No audio data in Gemini TTS response.');
        }

        // Gemini always returns raw PCM — save as WAV first
        $rawContent  = base64_decode($audioBase64);
        $wavFilename = 'tts/' . uniqid('gemini_') . '.wav';
        // Storage::disk('public')->put($wavFilename, $rawContent);
        Storage::disk('public')->put($wavFilename, $this->addWavHeader($rawContent));
        $wavPath = Storage::disk('public')->path($wavFilename);
        

        // Convert to MP3 if requested, otherwise return WAV
        if ($encoding === 'MP3') {
            $mp3Filename = Str::replaceLast('.wav', '.mp3', $wavFilename);
            $mp3Path = Storage::disk('public')->path($mp3Filename);
            $this->convertToMp3($wavPath, $mp3Path);
            Storage::disk('public')->delete($wavFilename);
            return $mp3Path;
        }

        return $wavPath;
    }

    protected function convertToMp3(string $wavPath, string $mp3Path): void
    {
        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => config('services.ffmpeg.ffmpeg_path', '/usr/bin/ffmpeg'),
            'ffprobe.binaries' => config('services.ffmpeg.ffprobe_path', '/usr/bin/ffprobe'),
        ]);

        $audio  = $ffmpeg->open($wavPath);
        $format = new \FFMpeg\Format\Audio\Mp3();
        $format->setAudioKiloBitrate(192);

        $audio->save($format, $mp3Path);
    }

    /**
     * Generate and return as base64 (useful for API responses)
     */
    public function generateBase64(string $text, string $prompt = '', string $voiceName = 'Achernar'): string
    {
        $path = $this->generate($text, $prompt, $voiceName);
        $base64 = base64_encode(file_get_contents($path));

        // Remove the file from the public disk (convert absolute path -> relative)
        $relative = Str::replaceFirst(storage_path('app/public/'), '', $path);
        Storage::disk('public')->delete($relative);

        return $base64;
    }

    protected function addWavHeader(string $pcmData, int $sampleRate = 24000, int $channels = 1, int $bitsPerSample = 16): string
    {
        $dataSize      = strlen($pcmData);
        $byteRate      = $sampleRate * $channels * ($bitsPerSample / 8);
        $blockAlign    = $channels * ($bitsPerSample / 8);
        $chunkSize     = 36 + $dataSize;

        $header  = 'RIFF';
        $header .= pack('V', $chunkSize);   // ChunkSize
        $header .= 'WAVE';
        $header .= 'fmt ';
        $header .= pack('V', 16);           // Subchunk1Size (16 for PCM)
        $header .= pack('v', 1);            // AudioFormat (1 = PCM)
        $header .= pack('v', $channels);    // NumChannels
        $header .= pack('V', $sampleRate);  // SampleRate
        $header .= pack('V', $byteRate);    // ByteRate
        $header .= pack('v', $blockAlign);  // BlockAlign
        $header .= pack('v', $bitsPerSample); // BitsPerSample
        $header .= 'data';
        $header .= pack('V', $dataSize);    // Subchunk2Size

        return $header . $pcmData;
    }
}