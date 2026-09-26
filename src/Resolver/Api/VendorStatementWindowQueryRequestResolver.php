<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Resolver\Api;

use App\Vendoring\DTO\Api\VendorStatementWindowQueryRequestDTO;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ResolverInterface\Api\VendorStatementWindowQueryRequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class VendorStatementWindowQueryRequestResolver implements VendorStatementWindowQueryRequestResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function resolve(Request $request): VendorStatementWindowQueryRequestDTO
    {
        $dto = new VendorStatementWindowQueryRequestDTO(
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
