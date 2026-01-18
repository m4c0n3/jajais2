<?php

namespace App\Support\Observability;

class RequestContext
{
    public static function currentRequestId(): ?string
    {
        return request()->headers->get('X-Request-Id');
    }
}
