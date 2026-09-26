<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Exception;

use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use PHPUnit\Framework\TestCase;

final class VendorApiQueryValidationExceptionTest extends TestCase
{
    public function testFromConstraintMessageMapsKnownVendorCodeToHint(): void
    {
        $exception = VendorApiQueryValidationException::fromConstraintMessage('vendor_id_required');

        self::assertSame('vendor_id_required', $exception->errorCode());
        self::assertSame('Provide the vendorId query parameter.', $exception->hint());
        self::assertSame('vendor_id_required', $exception->getMessage());
    }

    public function testFromConstraintMessageMapsUnknownCodeToFallback(): void
    {
        $exception = VendorApiQueryValidationException::fromConstraintMessage('unexpected_validator_message');

        self::assertSame('query_validation_error', $exception->errorCode());
        self::assertSame('Check required query parameters and try again.', $exception->hint());
        self::assertSame('query_validation_error', $exception->getMessage());
    }
}
