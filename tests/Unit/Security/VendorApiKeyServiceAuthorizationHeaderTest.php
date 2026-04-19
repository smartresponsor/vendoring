<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\Vendoring\Service\VendorApiKeyService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorApiKeyServiceAuthorizationHeaderTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private VendorApiKeyRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(VendorApiKeyRepositoryInterface::class);
    }

    public function testValidateAuthorizationHeaderReturnsNullWhenHeaderMissing(): void
    {
        $service = new VendorApiKeyService($this->entityManager, $this->repository);

        $this->repository->expects(self::never())->method('findActiveByToken');
        $this->entityManager->expects(self::never())->method('flush');

        self::assertNull($service->validateAuthorizationHeader('', 'write:transactions'));
    }

    public function testValidateAuthorizationHeaderValidatesBearerTokenWithPermission(): void
    {
        $vendor = new Vendor('Vendor A');
        $apiKey = new VendorApiKey($vendor, hash('sha256', 'plain-token'), 'write:transactions');
        $service = new VendorApiKeyService($this->entityManager, $this->repository);

        $this->repository
            ->expects(self::once())
            ->method('findActiveByToken')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);

        $this->entityManager->expects(self::once())->method('flush');

        self::assertSame($vendor, $service->validateAuthorizationHeader('Bearer plain-token', 'write:transactions'));
    }

    public function testValidateAuthorizationHeaderRejectsUnderScopedToken(): void
    {
        $vendor = new Vendor('Vendor A');
        $apiKey = new VendorApiKey($vendor, hash('sha256', 'plain-token'), 'read:transactions');
        $service = new VendorApiKeyService($this->entityManager, $this->repository);

        $this->repository
            ->expects(self::once())
            ->method('findActiveByToken')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);

        $this->entityManager->expects(self::never())->method('flush');

        self::assertNull($service->validateAuthorizationHeader('Bearer plain-token', 'write:transactions'));
    }
}
