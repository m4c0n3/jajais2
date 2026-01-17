<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Support\System\SystemSettings;
use App\Support\System\SystemInstaller;

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

Route::middleware('web')->group(function () {
    Route::get('/install', function (SystemSettings $settings) {
        if ($settings->isInitialized()) {
            abort(404);
        }

        return response()->view('install', [
            'mode' => request()->old('mode', 'client'),
        ]);
    })->name('install.form');

    Route::post('/install', function (SystemInstaller $installer) {
        if (app(SystemSettings::class)->isInitialized()) {
            abort(404);
        }

        $data = request()->validate([
            'mode' => ['required', 'in:client,control-plane'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $installer->install(
            $data['mode'],
            $data['admin_name'],
            $data['admin_email'],
            $data['admin_password'],
        );

        return redirect('/')->with('status', 'Installed');
    })->name('install.submit');
});
