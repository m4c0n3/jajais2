<?php

namespace Modules\ControlPlaneCore\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\ControlPlaneCore\Models\Entitlement;
use Modules\ControlPlaneCore\Models\Instance;
use Modules\ControlPlaneCore\Services\TokenIssuer;

class LicenseRefreshController
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'instance_uuid' => ['required', 'uuid'],
            'instance_secret' => ['required', 'string'],
            'module_ids' => ['nullable', 'array'],
        ]);

        $instance = Instance::where('uuid', $data['instance_uuid'])->first();

        if (!$instance || !Hash::check($data['instance_secret'], $instance->api_key_hash)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $entitlements = Entitlement::where('instance_id', $instance->id)
            ->where('enabled', true)
            ->get();

        $modules = $entitlements->pluck('module_id')->unique()->values()->all();

        $validTo = $entitlements->max('valid_to');
        $graceTo = $entitlements->max('grace_to');

        $payload = [
            'iss' => config('control_plane.issuer', 'control-plane'),
            'aud' => config('control_plane.audience', 'jajais'),
            'exp' => now()->addHours(12)->timestamp,
            'modules' => $modules,
            'valid_to' => $validTo?->toIso8601String(),
            'grace_to' => $graceTo?->toIso8601String(),
        ];

        try {
            $jwt = app(TokenIssuer::class)->issue($payload);
        } catch (\Throwable $error) {
            return response()->json(['error' => $error->getMessage()], 500);
        }

        return response()->json([
            'token' => $jwt,
            'valid_to' => $payload['valid_to'],
            'grace_to' => $payload['grace_to'],
        ]);
    }
}
