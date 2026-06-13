<?php

declare(strict_types=1);

namespace App\Vendoring\Support\Http;

use Symfony\Component\HttpFoundation\Request;

trait VendorHttpRequestTrait
{
    private function requestValue(object $request, string $nameEntity): ?string
    {
        if (!$request instanceof Request) {
            return null;
        }

        $value = $request->attributes->get($nameEntity);
        if (is_scalar($value) && '' !== trim((string) $value)) {
            return trim((string) $value);
        }

        $value = $request->query->get($nameEntity);
        if (is_scalar($value) && '' !== trim((string) $value)) {
            return trim((string) $value);
        }

        return null;
    }
}
