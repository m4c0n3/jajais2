<?php

namespace Modules\ControlPlaneCore\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\ControlPlaneCore\Models\Instance;

class InstanceRegisterController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instance_uuid' => ['required', 'uuid'],
            'name' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $secret = Str::random(48);

        $instance = Instance::create([
            'uuid' => $data['instance_uuid'],
            'name' => $data['name'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'api_key_hash' => Hash::make($secret),
            'status' => 'active',
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'instance_uuid' => $instance->uuid,
            'instance_secret' => $secret,
            'registered_at' => $instance->created_at?->toIso8601String(),
        ]);
    }
}
