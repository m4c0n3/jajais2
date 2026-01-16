<?php

namespace App\Support\Modules;

class ModuleManifest
{
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public ?string $provider = null,
        public ?string $requiresCore = null,
        public bool $licenseRequired = false,
        public array $permissions = [],
        public array $routes = [],
        public array $healthchecks = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $id = self::stringValue($data['id'] ?? null, 'id');
        $name = self::stringValue($data['name'] ?? null, 'name');
        $version = self::stringValue($data['version'] ?? null, 'version');

        $provider = isset($data['provider']) ? self::nullableString($data['provider']) : null;
        $requiresCore = isset($data['requires_core']) ? self::nullableString($data['requires_core']) : null;
        $licenseRequired = (bool) ($data['license_required'] ?? false);

        return new self(
            $id,
            $name,
            $version,
            $provider,
            $requiresCore,
            $licenseRequired,
            is_array($data['permissions'] ?? null) ? $data['permissions'] : [],
            is_array($data['routes'] ?? null) ? $data['routes'] : [],
            is_array($data['healthchecks'] ?? null) ? $data['healthchecks'] : [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'version' => $this->version,
            'provider' => $this->provider,
            'requires_core' => $this->requiresCore,
            'license_required' => $this->licenseRequired,
            'permissions' => $this->permissions,
            'routes' => $this->routes,
            'healthchecks' => $this->healthchecks,
        ];
    }

    private static function stringValue(mixed $value, string $field): string
    {
        if (!is_string($value) || $value === '') {
            throw new \InvalidArgumentException("Manifest missing required field: {$field}");
        }

        return $value;
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
