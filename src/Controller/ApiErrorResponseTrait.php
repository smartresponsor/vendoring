<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiErrorResponseTrait
{
    protected function validationErrorResponse(string $errorCode, string $hint): JsonResponse
    {
        return new JsonResponse([
            'error' => $errorCode,
            'hint' => $hint,
        ], 422);
    }

    protected function runtimeErrorResponse(string $errorCode, string $hint): JsonResponse
    {
        return new JsonResponse([
            'error' => $errorCode,
            'hint' => $hint,
        ], 500);
    }
}
