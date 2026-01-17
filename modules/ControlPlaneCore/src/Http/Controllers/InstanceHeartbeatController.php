<?php

namespace Modules\ControlPlaneCore\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\ControlPlaneCore\Models\Instance;

class InstanceHeartbeatController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instance_uuid' => ['required', 'uuid'],
            'instance_secret' => ['required', 'string'],
            'status' => ['nullable', 'array'],
        ]);

        $instance = Instance::where('uuid', $data['instance_uuid'])->first();

        if (!$instance || !Hash::check($data['instance_secret'], $instance->api_key_hash)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $instance->update([
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
