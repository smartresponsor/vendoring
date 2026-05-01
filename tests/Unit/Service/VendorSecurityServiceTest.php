<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\Service\Security\VendorSecurityService;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorSecurityServiceTest extends TestCase
{
    private VendorApiKeyServiceInterface&MockObject $apiKeys;

    protected function setUp(): void
    {
        $this->apiKeys = $this->createMock(VendorApiKeyServiceInterface::class);
    }

    public function testCreateKeyDelegatesToCanonicalApiKeyService(): void
    {
        $vendor = new Vendor('Vendor Example');

        $this->apiKeys
            ->expects(self::once())
            ->method('createKey')
            ->with($vendor, 'read,write')
            ->willReturn('plain-token');

        $service = new VendorSecurityService($this->apiKeys);

        self::assertSame('plain-token', $service->createKey($vendor, 'read,write'));
    }

    public function testRotateAndRevokeDelegateToCanonicalApiKeyService(): void
    {
        $apiKey = new VendorApiKey(new Vendor('Vendor Example'), hash('sha256', 'plain-token'), 'read');

        $this->apiKeys
            ->expects(self::once())
            ->method('rotateKey')
            ->with($apiKey)
            ->willReturn('new-token');
        $this->apiKeys
            ->expects(self::once())
            ->method('revokeKey')
            ->with($apiKey);

        $service = new VendorSecurityService($this->apiKeys);

        self::assertSame('new-token', $service->rotateKey($apiKey));
        $service->revokeKey($apiKey);
    }

    public function testValidateTokenAndResolveAuthHeaderDelegateToCanonicalApiKeyService(): void
    {
        $vendor = new Vendor('Vendor Example');

        $this->apiKeys
            ->expects(self::once())
            ->method('validateToken')
            ->with('plain-token', 'read')
            ->willReturn($vendor);
        $this->apiKeys
            ->expects(self::once())
            ->method('resolveVendorFromAuthHeader')
            ->with('Bearer plain-token')
            ->willReturn($vendor);

        $service = new VendorSecurityService($this->apiKeys);

        self::assertSame($vendor, $service->validateToken('plain-token', 'read'));
        self::assertSame($vendor, $service->resolveVendorFromAuthHeader('Bearer plain-token'));
    }
}
