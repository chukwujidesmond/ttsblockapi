<?php

namespace App\Http\Controllers;
use App\Services\GeminiTTSService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class SpeechController  extends Controller
{
   

    public function __construct(protected GeminiTTSService $ttsService)
    {
        $this->middleware('auth:sanctum');
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
                prompt:    $request->input('prompt', ''),
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

    public function voices()
    {
        return response()->json([
            'success' => true,
            'voices'  => config('gemini_voices.voices'),
            'models'  => config('gemini_voices.models'),
        ]);
    }

}
