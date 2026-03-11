<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpeechController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/speech', [SpeechController::class, 'index']);
Route::post('/speech/generate',      [SpeechController::class, 'generateAudio']);
Route::post('/speech/generate-json', [SpeechController::class, 'generateAudioJson']);

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');