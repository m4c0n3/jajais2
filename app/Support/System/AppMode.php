<?php

namespace App\Support\System;

class AppMode
{
    public const CLIENT = 'client';
    public const CONTROL_PLANE = 'control-plane';

    public function __construct(private SystemSettings $settings, private ModuleSetResolver $resolver)
    {
    }

    public function current(): ?string
    {
        return $this->settings->mode() ?? $this->resolver->modeFromEnv();
    }

    public function isControlPlane(): bool
    {
        return $this->current() === self::CONTROL_PLANE;
    }
}
