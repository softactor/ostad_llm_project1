<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::controller(ChatController::class)->group(function () {
    Route::get('/chat',        'index')->name('chat.index');
    Route::post('/chat/send',  'send')->name('chat.send');
    Route::post('/chat/clear', 'clear')->name('chat.clear');
});