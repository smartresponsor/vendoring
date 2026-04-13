<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Service\Api;

use App\Service\Api\TenantQueryRequestResolver;
use App\Exception\ApiQueryValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class TenantQueryRequestResolverTest extends TestCase
{
    public function testResolveReturnsDtoForValidTenantQuery(): void
    {
        $resolver = new TenantQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $dto = $resolver->resolve(new Request(['tenantId' => 'tenant-1']));

        self::assertSame('tenant-1', $dto->tenantId);
    }

    public function testResolveThrowsWhenTenantQueryIsMissing(): void
    {
        $resolver = new TenantQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $this->expectException(ApiQueryValidationException::class);
        $this->expectExceptionMessage('tenant_id_required');

        $resolver->resolve(new Request());
    }
}
