<?php

namespace App\Support\Audit;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditService
{
    public function log(string $action, array $context = []): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        $actorType = 'system';
        $actorId = null;

        if (Auth::check()) {
            $actorType = 'user';
            $actorId = Auth::id();
        }

        $ip = null;
        $userAgent = null;

        if (app()->bound('request')) {
            $request = request();
            $ip = $request->ip();
            $userAgent = $request->userAgent();
        }

        DB::table('audit_logs')->insert([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $context['target_type'] ?? null,
            'target_id' => $context['target_id'] ?? null,
            'metadata' => isset($context['metadata']) ? json_encode($context['metadata']) : null,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => CarbonImmutable::now(),
        ]);
    }
}
