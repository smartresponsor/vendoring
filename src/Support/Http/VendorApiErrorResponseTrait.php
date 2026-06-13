<?php

declare(strict_types=1);

namespace App\Vendoring\Support\Http;

trait VendorApiErrorResponseTrait
{
    /** @param array<string, mixed> $payload */
    private function apiErrorResponse(string $reason, array $payload = []): array
    {
        return [
            'ok' => false,
            'component' => 'vendoring',
            'reason' => $reason,
            'payload' => $payload,
        ];
    }
}
