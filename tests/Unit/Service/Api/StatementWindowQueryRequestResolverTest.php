<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\StatementWindowQueryRequestResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class StatementWindowQueryRequestResolverTest extends TestCase
{
    public function testResolveReturnsDtoForValidStatementWindowQuery(): void
    {
        $resolver = new StatementWindowQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $dto = $resolver->resolve(new Request([
            'tenantId' => 'tenant-1',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
        ]));

        self::assertSame('tenant-1', $dto->tenantId);
        self::assertSame('2026-03-01', $dto->from);
        self::assertSame('2026-03-31', $dto->to);
        self::assertSame('USD', $dto->currency);
    }

    public function testResolveThrowsWhenStatementWindowQueryIsMissing(): void
    {
        $resolver = new StatementWindowQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tenant_id_required');

        $resolver->resolve(new Request());
    }
}
