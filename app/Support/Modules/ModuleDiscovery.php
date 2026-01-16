<?php

namespace App\Support\Modules;

use Illuminate\Support\Facades\File;

class ModuleDiscovery
{
    /**
     * @return array<int, ModuleManifest>
     */
    public function discover(): array
    {
        $path = (string) config('modules.path', base_path('modules'));

        if (!is_dir($path)) {
            return [];
        }

        $manifests = [];
        $files = File::glob($path.'/*/module.json') ?: [];

        foreach ($files as $file) {
            $manifests[] = $this->parseManifest($file);
        }

        return $manifests;
    }

    public function parseManifest(string $path): ModuleManifest
    {
        $contents = File::get($path);
        $data = json_decode($contents, true);

        if (!is_array($data)) {
            throw new \RuntimeException("Invalid JSON in manifest: {$path}");
        }

        return ModuleManifest::fromArray($data);
    }
}
