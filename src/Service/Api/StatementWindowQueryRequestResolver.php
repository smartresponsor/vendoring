<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Api;

use App\Vendoring\DTO\Api\StatementWindowQueryRequestDTO;
use App\Vendoring\Exception\ApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class StatementWindowQueryRequestResolver implements StatementWindowQueryRequestResolverInterface
{
    public function __construct(private ValidatorInterface $validator) {}

    public function resolve(Request $request): StatementWindowQueryRequestDTO
    {
        $dto = new StatementWindowQueryRequestDTO(
            tenantId: trim((string) $request->query->get('tenantId', '')),
            from: trim((string) $request->query->get('from', '')),
            to: trim((string) $request->query->get('to', '')),
            currency: trim((string) $request->query->get('currency', 'USD')),
        );

        $violations = $this->validator->validate($dto);
        if (0 !== $violations->count()) {
            $firstViolation = $violations->get(0);
            throw ApiQueryValidationException::fromConstraintMessage((string) $firstViolation->getMessage());
        }

        return $dto;
    }
}
