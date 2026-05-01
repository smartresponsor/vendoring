<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Api;

use App\Vendoring\DTO\Api\VendorStatementWindowQueryRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class VendorStatementWindowQueryRequestResolverService implements VendorStatementWindowQueryRequestResolverServiceInterface
{
    public function __construct(private ValidatorInterface $validator) {}

    public function resolve(Request $request): VendorStatementWindowQueryRequestDTO
    {
        $dto = new VendorStatementWindowQueryRequestDTO(
            tenantId: trim((string) $request->query->get('tenantId', '')),
            from: trim((string) $request->query->get('from', '')),
            to: trim((string) $request->query->get('to', '')),
            currency: trim((string) $request->query->get('currency', 'USD')),
        );

        $violations = $this->validator->validate($dto);
        if (0 !== $violations->count()) {
            $firstViolation = $violations->get(0);
            throw VendorApiQueryValidationException::fromConstraintMessage((string) $firstViolation->getMessage());
        }

        return $dto;
    }
}
