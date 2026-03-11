<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CleanAudioJob;
use App\Services\AudioCleanerService;

class AudioController extends Controller
{
    
    // ── Option A: Synchronous (small files < 10MB) ──────────────────────────
    public function uploadSync(Request $request, AudioCleanerService $cleaner)
    {
        $request->validate([
            'audio'    => 'required|file|mimes:wav,mp3,ogg,flac|max:10240',
            'strength' => 'sometimes|numeric|min:0|max:1',
            'model'    => 'sometimes|in:deepfilter,noisereduce',
        ]);

        $result = $cleaner->process($request->file('audio'), [
            'strength' => $request->input('strength', 0.85),
            'model'    => $request->input('model', 'deepfilter'),
        ]);

        return response()->json([
            'message'      => 'Audio cleaned successfully',
            'download_url' => $result['download_url'],
            'id'           => $result['id'],
            'model_used'   => $result['model_used'],
        ]);
    }

    // ── Option B: Async via Queue (large files > 10MB) ───────────────────────
    public function uploadAsync(Request $request)
    {
        $request->validate([
            'audio'    => 'required|file|mimes:wav,mp3,ogg,flac|max:102400', // 100MB
            'strength' => 'sometimes|numeric|min:0|max:1',
            'model'    => 'sometimes|in:deepfilter,noisereduce',
        ]);

        $file     = $request->file('audio');
        $id       = \Str::uuid()->toString();
        $rawPath  = "audio/raw/{$id}." . $file->getClientOriginalExtension();

        Storage::put($rawPath, file_get_contents($file->getRealPath()));

        CleanAudioJob::dispatch($id, $rawPath, [
            'strength' => $request->input('strength', 0.85),
            'model'    => $request->input('model', 'deepfilter'),
        ]);

        return response()->json([
            'message'    => 'Processing started',
            'job_id'     => $id,
            'status_url' => route('audio.status', $id),
        ], 202);
    }

    // ── Poll status (for async) ───────────────────────────────────────────────
    public function status(string $jobId)
    {
        $result = cache("audio:{$jobId}");

        if (!$result) {
            return response()->json(['status' => 'processing']);
        }

        return response()->json($result);
    }
}
