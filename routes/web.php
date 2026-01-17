<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::middleware(['auth', 'permission:admin.access'])->get('/admin/locale/{locale}', function (string $locale) {
    $allowed = ['sk', 'en'];

    if (!in_array($locale, $allowed, true)) {
        abort(404);
    }

    $user = Auth::user();

    if ($user) {
        $user->forceFill(['locale' => $locale])->save();
    }

    session(['app_locale' => $locale]);
    app()->setLocale($locale);

    return redirect()->back();
})->name('admin.locale.switch');
