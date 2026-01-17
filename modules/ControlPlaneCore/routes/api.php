<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlPlaneCore\Http\Controllers\InstanceRegisterController;
use Modules\ControlPlaneCore\Http\Controllers\InstanceHeartbeatController;
use Modules\ControlPlaneCore\Http\Controllers\LicenseRefreshController;

Route::prefix('api/v1')->group(function () {
    Route::post('/instances/register', InstanceRegisterController::class);
    Route::post('/instances/heartbeat', InstanceHeartbeatController::class);
    Route::post('/licenses/refresh', LicenseRefreshController::class);
});
