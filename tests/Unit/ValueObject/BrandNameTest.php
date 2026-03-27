<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\BrandName;
use PHPUnit\Framework\TestCase;

final class BrandNameTest extends TestCase
{
    public function testFromRawTrimsWhitespace(): void
    {
        $value = BrandName::fromRaw('  Smartresponsor  ');

        self::assertSame('Smartresponsor', $value->value());
    }

    public function testFromRawRejectsBlankStringAfterTrim(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');

        BrandName::fromRaw('   ');
    }

    public function testFromRawRejectsTooLongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_too_long');

        BrandName::fromRaw(str_repeat('a', 256));
    }
}
