<?php

namespace App\Http\Middleware;

use App\Support\Licensing\LicenseService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleActive
{
    public function handle(Request $request, Closure $next, string $moduleId): Response
    {
        $module = DB::table('modules')->where('id', $moduleId)->first();

        if (!$module) {
            abort(403, 'Module not registered.');
        }

        if (!$module->enabled) {
            abort(403, 'Module is disabled.');
        }

        if ($module->license_required) {
            $licenseService = app(LicenseService::class);

            if (!$licenseService->isModuleEntitled($moduleId)) {
                abort(403, 'Module license is not valid.');
            }
        }

        return $next($request);
    }
}
