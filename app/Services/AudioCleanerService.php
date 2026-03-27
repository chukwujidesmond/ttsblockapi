<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioCleanerService
{
    private string $pythonUrl;
    private string $secret;

    public function __construct()
    {
        $this->pythonUrl = config('services.audio_cleaner.url');    // http://audio-service:8000
        $this->secret    = config('services.audio_cleaner.secret');
    }

    /**
     * Full pipeline:
     * 1. Save original upload
     * 2. Send to Python for cleaning
     * 3. Store cleaned file
     * 4. Return paths
     */
    public function process(UploadedFile $file, array $options = []): array
    {
        $id           = Str::uuid();
        $originalPath = "audio/raw/{$id}." . $file->getClientOriginalExtension();
        $cleanedPath  = "audio/cleaned/{$id}_clean.wav";

        // FIX 5: read the file once
        $rawContents = file_get_contents($file->getRealPath());

        // 1. Save original
        Storage::put($originalPath, $rawContents);

        // 2. Send to Python
        $response = Http::timeout(300)
            ->withHeaders(['X-Api-Secret' => $this->secret])
            ->attach('file', $rawContents, $file->getClientOriginalName())
            ->post($this->pythonUrl . '/clean', [
                'strength' => $options['strength'] ?? 0.85,
                'model'    => $options['model']    ?? 'deepfilter',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Audio cleaner failed: ' . $response->body());
        }

        // 3. Store cleaned audio
        Storage::put($cleanedPath, $response->body());

        return [
            'id'            => $id,
            'original_path' => $originalPath,
            'cleaned_path'  => $cleanedPath,
            'download_url'  => Storage::url($cleanedPath),
            'model_used'    => $response->header('X-Processing-Model'),
        ];
    }
    
}