<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\Projection\VendorPublicProfileAttachmentProjection;
use App\Vendoring\Resolver\Profile\VendorChainedProfileAttachmentResolver;
use App\Vendoring\ResolverInterface\Profile\VendorProfileAttachmentResolverInterface;
use PHPUnit\Framework\TestCase;

final class VendorOptionalAttachmentBridgeContractTest extends TestCase
{
    public function testFallbackIsUsedOnlyWhenPrimaryIsEmpty(): void
    {
        $legacy = new VendorPublicProfileAttachmentProjection(null, '/legacy.png');
        $primary = $this->createMock(VendorProfileAttachmentResolverInterface::class);
        $fallback = $this->createMock(VendorProfileAttachmentResolverInterface::class);
        $primary->expects(self::once())->method('resolvePrimaryForVendorSlot')->with(42, 'avatar')->willReturn(VendorPublicProfileAttachmentProjection::empty());
        $fallback->expects(self::once())->method('resolvePrimaryForVendorSlot')->with(42, 'avatar')->willReturn($legacy);
        self::assertSame($legacy, (new VendorChainedProfileAttachmentResolver($primary, $fallback))->resolvePrimaryForVendorSlot(42, 'avatar'));
    }

    public function testPrimaryTakesPrecedence(): void
    {
        $attachment = new VendorPublicProfileAttachmentProjection(9, '/primary.png');
        $primary = $this->createMock(VendorProfileAttachmentResolverInterface::class);
        $fallback = $this->createMock(VendorProfileAttachmentResolverInterface::class);
        $primary->expects(self::once())->method('resolvePrimaryForVendorSlot')->with(42, 'avatar')->willReturn($attachment);
        $fallback->expects(self::never())->method('resolvePrimaryForVendorSlot');
        self::assertSame($attachment, (new VendorChainedProfileAttachmentResolver($primary, $fallback))->resolvePrimaryForVendorSlot(42, 'avatar'));
    }
}
