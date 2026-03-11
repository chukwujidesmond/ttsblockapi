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

        // 1. Save original
        Storage::put($originalPath, file_get_contents($file->getRealPath()));

        // 2. Send to Python → get cleaned binary back
        $response = Http::timeout(300)              // 5min for large files
            ->withHeaders(['X-Api-Secret' => $this->secret])
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($this->pythonUrl . '/clean', [
                'strength' => $options['strength'] ?? 0.85,
                'model'    => $options['model']    ?? 'deepfilter',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Audio cleaner failed: ' . $response->body());
        }

        // 3. Store cleaned audio (response body IS the wav file)
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