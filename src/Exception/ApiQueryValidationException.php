<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Exception;

use InvalidArgumentException;

final class ApiQueryValidationException extends InvalidArgumentException
{
    public function __construct(
        private readonly string $errorCode,
        private readonly string $hint,
    ) {
        parent::__construct($errorCode);
    }

    public static function fromConstraintMessage(string $message): self
    {
        return match (trim($message)) {
            'tenant_id_required' => new self('tenant_id_required', 'Provide the tenantId query parameter.'),
            'statement_from_required' => new self('statement_from_required', 'Provide the from query parameter.'),
            'statement_to_required' => new self('statement_to_required', 'Provide the to query parameter.'),
            default => new self(
                'query_validation_error',
                'Check required query parameters and try again.',
            ),
        };
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function hint(): string
    {
        return $this->hint;
    }
}
