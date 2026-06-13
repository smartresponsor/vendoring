<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Navigation;

use App\Vendoring\Service\Profile\VendorPublicProfileUrlGeneratorService;
use PHPUnit\Framework\TestCase;

final class VendorPublicProfileUrlGeneratorServiceTest extends TestCase
{
    public function testItGeneratesDefaultVendorProfilePath(): void
    {
        $generator = new VendorPublicProfileUrlGeneratorService();

        self::assertSame('/vendor/42', $generator->generateForVendorId(42));
    }

    public function testItSupportsHostTemplateOverride(): void
    {
        $generator = new VendorPublicProfileUrlGeneratorService('/business/vendor/{vendorId}');

        self::assertSame('/business/vendor/42', $generator->generateForVendorId(42));
    }
}
