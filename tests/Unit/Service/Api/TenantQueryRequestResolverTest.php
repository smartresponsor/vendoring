<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service\Api;

use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\Resolver\Api\VendorTenantQueryRequestResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class TenantQueryRequestResolverTest extends TestCase
{
    public function testResolveReturnsDtoForValidVendorQuery(): void
    {
        $resolver = new VendorTenantQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $dto = $resolver->resolve(new Request(['vendorId' => 'vendor-1']));

        self::assertSame('vendor-1', $dto->vendorId);
    }

    public function testResolveThrowsWhenVendorQueryIsMissing(): void
    {
        $resolver = new VendorTenantQueryRequestResolver(Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator());

        $this->expectException(VendorApiQueryValidationException::class);
        $this->expectExceptionMessage('vendor_id_required');

        $resolver->resolve(new Request());
    }
}
