<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Api;

use App\DTO\Api\StatementWindowQueryRequestDTO;
use App\ServiceInterface\Api\StatementWindowQueryRequestResolverInterface;
use InvalidArgumentException;
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
            throw new InvalidArgumentException((string) $firstViolation?->getMessage());
        }

        return $dto;
    }
}
