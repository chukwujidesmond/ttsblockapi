<?php

namespace Tests\Unit;

use App\Services\GeminiTTSService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeminiTTSServiceTest extends TestCase
{
    public function test_generate_sends_correct_payload_and_saves_file(): void
    {
        Storage::fake('local');
        Http::fake(function ($request) {
            // ensure URL includes key parameter
            $this->assertStringContainsString('?key=example-key', $request->url());

            $body = json_decode((string) $request->body(), true);

            // payload structure assertions
            $this->assertEquals('gemini-2.5-pro-tts', $body['model']);
            $this->assertArrayHasKey('generationConfig', $body);
            $speech = $body['generationConfig']['speechConfig'];
            $this->assertArrayHasKey('voiceConfig', $speech);
            $this->assertArrayNotHasKey('audioConfig', $speech);
            // audioConfig belongs to generationConfig root now
            $audio = $body['generationConfig']['audioConfig'];
            $this->assertEquals('MP3', $audio['audioEncoding']);
            $this->assertEquals(0, $audio['pitch']);
            $this->assertEquals(1, $audio['speakingRate']);
            // ensure languageCode not sent at all
            $this->assertArrayNotHasKey('languageCode', $speech['voiceConfig']['prebuiltVoiceConfig']);

            // return a dummy response mimicking API
            $dummyAudio = base64_encode('dummy');
            return Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['inlineData' => ['data' => $dummyAudio]],
                            ],
                        ],
                    ],
                ],
            ], 200);
        });

        // configure something so service uses known url/key
        config(['services.gemini.api_key' => 'example-key']);
        config(['services.gemini.tts_url' => 'https://example.com/tts:generateContent']);

        $service = new GeminiTTSService();
        $path = $service->generate('hello world', 'prompt', 'Achernar', 'en-us', 'MP3', 0, 1);

        // assert file was written
        Storage::disk('local')->assertExists(str_replace(storage_path('app/') , '', $path));

        $this->assertStringEndsWith('.mp3', $path);
    }
}
