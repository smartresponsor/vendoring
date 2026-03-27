<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorApiKey;
use App\Service\VendorSecurityService;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorSecurityServiceTest extends TestCase
{
    private VendorApiKeyServiceInterface&MockObject $apiKeyService;

    protected function setUp(): void
    {
        $this->apiKeyService = $this->createMock(VendorApiKeyServiceInterface::class);
    }

    public function testCreateKeyDelegatesToApiKeyService(): void
    {
        $vendor = $this->createMock(Vendor::class);
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('createKey')
            ->with($vendor, 'read:transactions')
            ->willReturn('plain-token');

        self::assertSame('plain-token', $service->createKey($vendor, 'read:transactions'));
    }

    public function testRotateKeyDelegatesToApiKeyService(): void
    {
        $existingKey = $this->createMock(VendorApiKey::class);
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('rotateKey')
            ->with($existingKey)
            ->willReturn('rotated-token');

        self::assertSame('rotated-token', $service->rotateKey($existingKey));
    }

    public function testRevokeKeyDelegatesToApiKeyService(): void
    {
        $key = $this->createMock(VendorApiKey::class);
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('revokeKey')
            ->with($key);

        $service->revokeKey($key);
    }

    public function testValidateTokenDelegatesToApiKeyService(): void
    {
        $vendor = $this->createMock(Vendor::class);
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('validateToken')
            ->with('token', 'write:transactions')
            ->willReturn($vendor);

        self::assertSame($vendor, $service->validateToken('token', 'write:transactions'));
    }

    public function testResolveVendorFromAuthHeaderDelegatesToApiKeyService(): void
    {
        $vendor = $this->createMock(Vendor::class);
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('resolveVendorFromAuthHeader')
            ->with('Bearer token')
            ->willReturn($vendor);

        self::assertSame($vendor, $service->resolveVendorFromAuthHeader('Bearer token'));
    }
}
