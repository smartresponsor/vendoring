<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\Service\Security\VendorSecurityService;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
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
        $vendor = new Vendor('Vendor A');
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
        $existingKey = new VendorApiKey(new Vendor('Vendor A'), 'hash', 'read:transactions');
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
        $key = new VendorApiKey(new Vendor('Vendor A'), 'hash', 'read:transactions');
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('revokeKey')
            ->with($key);

        $service->revokeKey($key);
    }

    public function testValidateTokenDelegatesToApiKeyService(): void
    {
        $vendor = new Vendor('Vendor A');
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('validateToken')
            ->with('token', 'write:transactions')
            ->willReturn($vendor);

        self::assertSame($vendor, $service->validateToken('token', 'write:transactions'));
    }


    public function testValidateAuthorizationHeaderDelegatesToApiKeyService(): void
    {
        $vendor = new Vendor('Vendor A');
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('validateAuthorizationHeader')
            ->with('Bearer token', 'write:transactions')
            ->willReturn($vendor);

        self::assertSame($vendor, $service->validateAuthorizationHeader('Bearer token', 'write:transactions'));
    }

    public function testResolveVendorFromAuthHeaderDelegatesToApiKeyService(): void
    {
        $vendor = new Vendor('Vendor A');
        $service = new VendorSecurityService($this->apiKeyService);

        $this->apiKeyService
            ->expects(self::once())
            ->method('resolveVendorFromAuthHeader')
            ->with('Bearer token')
            ->willReturn($vendor);

        self::assertSame($vendor, $service->resolveVendorFromAuthHeader('Bearer token'));
    }
}
