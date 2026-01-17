<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => config('app.version'),
        'time' => now()->toIso8601String(),
    ]);
});
