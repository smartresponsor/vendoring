<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\ValueObject;

use App\Vendoring\ValueObject\VendorBrandNameValueObject;
use PHPUnit\Framework\TestCase;

final class BrandNameTest extends TestCase
{
    public function testFromRawTrimsWhitespace(): void
    {
        $value = VendorBrandNameValueObject::fromRaw('  Smartresponsor  ');

        self::assertSame('Smartresponsor', $value->value());
    }

    public function testFromRawRejectsBlankStringAfterTrim(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');

        VendorBrandNameValueObject::fromRaw('   ');
    }

    public function testFromRawRejectsTooLongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_too_long');

        VendorBrandNameValueObject::fromRaw(str_repeat('a', 256));
    }
}
