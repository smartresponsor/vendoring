<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Api;

use App\Vendoring\DTO\Api\TenantQueryRequestDTO;
use App\Vendoring\Exception\ApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\TenantQueryRequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class TenantQueryRequestResolver implements TenantQueryRequestResolverInterface
{
    public function __construct(private ValidatorInterface $validator) {}

    public function resolve(Request $request): TenantQueryRequestDTO
    {
        $dto = new TenantQueryRequestDTO(
            tenantId: trim((string) $request->query->get('tenantId', '')),
        );

        $violations = $this->validator->validate($dto);
        if (0 !== $violations->count()) {
            $firstViolation = $violations->get(0);
            throw ApiQueryValidationException::fromConstraintMessage((string) $firstViolation->getMessage());
        }

        return $dto;
    }
}
