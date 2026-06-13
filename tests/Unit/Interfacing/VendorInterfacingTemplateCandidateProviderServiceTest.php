<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Interfacing;

use App\Vendoring\Service\Interfacing\VendorInterfacingTemplateCandidateProviderService;
use App\Vendoring\ValueObject\Interfacing\VendorInterfacingSurfaceNameValueObject;
use PHPUnit\Framework\TestCase;

final class VendorInterfacingTemplateCandidateProviderServiceTest extends TestCase
{
    public function testProfileCandidatesUseVendorNounSurface(): void
    {
        $provider = new VendorInterfacingTemplateCandidateProviderService();

        $candidates = $provider->candidatesFor(VendorInterfacingSurfaceNameValueObject::PROFILE);

        self::assertContains('@Interfacing/vendor/profile/show.html.twig', $candidates);
        self::assertContains('vendor/profile/show.html.twig', $candidates);
        self::assertNotContains('@Interfacing/vendoring/profile/show.html.twig', $candidates);
        self::assertNotContains('interfacing/vendor/profile/show.html.twig', $candidates);
    }

    public function testIndexCandidatesUseVendorBaseTemplate(): void
    {
        $provider = new VendorInterfacingTemplateCandidateProviderService();

        $candidates = $provider->candidatesFor(VendorInterfacingSurfaceNameValueObject::INDEX);

        self::assertSame('@Interfacing/vendor/index.html.twig', $candidates[0]);
        self::assertContains('vendor/index.html.twig', $candidates);
        self::assertNotContains('@Interfacing/vendoring/index.html.twig', $candidates);
    }
}
