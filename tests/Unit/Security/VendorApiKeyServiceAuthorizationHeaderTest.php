<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\Vendoring\Service\Security\VendorApiKeyService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorApiKeyServiceAuthorizationHeaderTest extends TestCase
{
    private VendorApiKeyRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorApiKeyRepositoryInterface::class);
    }

    public function testValidateAuthorizationHeaderReturnsNullWhenHeaderMissing(): void
    {
        $service = new VendorApiKeyService($this->repository);

        $this->repository->expects(self::never())->method('findActiveByTokenHash');

        self::assertNull($service->validateAuthorizationHeader('', 'write:transactions'));
    }

    public function testValidateAuthorizationHeaderValidatesBearerTokenWithPermission(): void
    {
        $vendor = new VendorEntity('Vendor A');
        $apiKey = new VendorApiKeyEntity($vendor, hash('sha256', 'plain-token'), 'write:transactions');
        $service = new VendorApiKeyService($this->repository);

        $this->repository
            ->expects(self::once())
            ->method('findActiveByTokenHash')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);

        $this->repository->expects(self::once())->method('save')->with($apiKey, true);

        self::assertSame($vendor, $service->validateAuthorizationHeader('Bearer plain-token', 'write:transactions'));
    }

    public function testValidateAuthorizationHeaderRejectsUnderScopedToken(): void
    {
        $vendor = new VendorEntity('Vendor A');
        $apiKey = new VendorApiKeyEntity($vendor, hash('sha256', 'plain-token'), 'read:transactions');
        $service = new VendorApiKeyService($this->repository);

        $this->repository
            ->expects(self::once())
            ->method('findActiveByTokenHash')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);

        self::assertNull($service->validateAuthorizationHeader('Bearer plain-token', 'write:transactions'));
    }
}
