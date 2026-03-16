<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('login', [LoginController::class, 'login']);
Route::post('register', [RegisterController::class, 'register']);
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::post('/create-voice-over', [SpeechController::class, 'createVoiceOver']);
Route::get('/voice-over/{slug}', [SpeechController::class, 'getVoiceOver']);
Route::get('/list-voice', [SpeechController::class, 'listVoice']);
Route::post('/speech/generate',      [SpeechController::class, 'generateAudio']);
Route::post('/speech/generate-json', [SpeechController::class, 'generateAudioJson']);
Route::post('/speech/process-transcription', [SpeechController::class, 'processAudioTranscription']);
