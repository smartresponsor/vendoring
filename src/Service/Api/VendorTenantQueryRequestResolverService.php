<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Api;

use App\Vendoring\DTO\Api\VendorTenantQueryRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorTenantQueryRequestResolverServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class VendorTenantQueryRequestResolverService implements VendorTenantQueryRequestResolverServiceInterface
{
    public function __construct(private ValidatorInterface $validator) {}

    public function resolve(Request $request): VendorTenantQueryRequestDTO
    {
        $dto = new VendorTenantQueryRequestDTO(
            tenantId: trim((string) $request->query->get('tenantId', '')),
        );

        $violations = $this->validator->validate($dto);
        if (0 !== $violations->count()) {
            $firstViolation = $violations->get(0);
            throw VendorApiQueryValidationException::fromConstraintMessage((string) $firstViolation->getMessage());
        }

        return $dto;
    }
}
