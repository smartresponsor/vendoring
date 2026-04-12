<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;

trait StatementQueryValidationResponseTrait
{
    protected function statementQueryValidationResponse(InvalidArgumentException $exception): JsonResponse
    {
        $errorCode = trim($exception->getMessage());

        return match ($errorCode) {
            'tenant_id_required' => $this->validationErrorResponse(
                'tenant_id_required',
                'Provide the tenantId query parameter.',
            ),
            'statement_from_required' => $this->validationErrorResponse(
                'statement_from_required',
                'Provide the from query parameter.',
            ),
            'statement_to_required' => $this->validationErrorResponse(
                'statement_to_required',
                'Provide the to query parameter.',
            ),
            default => $this->validationErrorResponse(
                'statement_params_required',
                'Provide tenantId, from, and to query parameters.',
            ),
        };
    }
}
