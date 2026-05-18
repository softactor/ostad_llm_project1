<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::controller(ChatController::class)->group(function () {
    Route::get('/chat',        'index')->name('chat.index');
    Route::post('/chat/send',  'send')->name('chat.send');
    Route::post('/chat/clear', 'clear')->name('chat.clear');
});


Route::controller(ImageController::class)->group(function () {
    Route::get('/image-generator',        'index')->name('image.index');
    Route::post('/image-generator/generate',  'generate')->name('image.generate');
    Route::post('/image-generator/clear', 'clear')->name('image.clear');
});