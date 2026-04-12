<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Api;

use App\DTO\Api\TenantQueryRequestDTO;
use App\ServiceInterface\Api\TenantQueryRequestResolverInterface;
use InvalidArgumentException;
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
            throw new InvalidArgumentException((string) $firstViolation?->getMessage());
        }

        return $dto;
    }
}
