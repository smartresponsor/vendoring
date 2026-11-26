<?php

declare(strict_types=1);

namespace App\Vendoring\Trait\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

trait VendorApiErrorResponseTrait
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{ok: false, component: 'vendoring', reason: string, payload: array<string, mixed>}
     */
    private function apiErrorResponse(string $reason, array $payload = []): array
    {
        return [
            'ok' => false,
            'component' => 'vendoring',
            'reason' => $reason,
            'payload' => $payload,
        ];
    }

    private function validationErrorResponse(string $errorCode, string $hint): JsonResponse
    {
        return new JsonResponse([
            'ok' => false,
            'component' => 'vendoring',
            'errorCode' => $errorCode,
            'hint' => $hint,
        ], 422);
    }

    private function runtimeErrorResponse(string $errorCode, string $hint): JsonResponse
    {
        return new JsonResponse([
            'ok' => false,
            'component' => 'vendoring',
            'errorCode' => $errorCode,
            'hint' => $hint,
        ], 500);
    }
}
