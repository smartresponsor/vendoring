<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Vendor;
use App\Entity\VendorApiKey;
use App\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\Service\VendorApiKeyService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorApiKeyServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private VendorApiKeyRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(VendorApiKeyRepositoryInterface::class);
    }

    public function testCreateKeyPersistsHashedTokenAndReturnsPlainToken(): void
    {
        $vendor = new Vendor('Vendor Example');

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function (VendorApiKey $apiKey) use ($vendor, &$plainToken): bool {
                self::assertSame($vendor, $apiKey->getVendor());
                self::assertSame('read,write', $apiKey->getPermissions());
                self::assertSame('active', $apiKey->getStatus());
                self::assertSame(hash('sha256', $plainToken), $apiKey->getTokenHash());

                return true;
            }), true);
        $this->entityManager->expects(self::never())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);
        $plainToken = $service->createKey($vendor, 'read,write');

        self::assertSame(64, strlen($plainToken));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $plainToken);
    }

    public function testRotateKeyDeactivatesExistingKeyAndSavesReplacement(): void
    {
        $vendor = new Vendor('Vendor Example');
        $existingKey = new VendorApiKey($vendor, hash('sha256', 'old-token'), 'read,write');

        $this->repository
            ->expects(self::exactly(2))
            ->method('save')
            ->withConsecutive(
                [self::callback(function (VendorApiKey $apiKey) use ($existingKey): bool {
                    self::assertSame($existingKey, $apiKey);
                    self::assertSame('inactive', $apiKey->getStatus());

                    return true;
                }), false],
                [self::callback(function (VendorApiKey $apiKey) use ($vendor, $existingKey, &$newPlainToken): bool {
                    self::assertSame($vendor, $apiKey->getVendor());
                    self::assertSame($existingKey->getPermissions(), $apiKey->getPermissions());
                    self::assertSame('active', $apiKey->getStatus());
                    self::assertSame(hash('sha256', $newPlainToken), $apiKey->getTokenHash());

                    return true;
                }), false],
            );
        $this->entityManager->expects(self::once())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);
        $newPlainToken = $service->rotateKey($existingKey);

        self::assertSame(64, strlen($newPlainToken));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $newPlainToken);
    }

    public function testValidateTokenRespectsPermissionGateAndTouchesActiveKeyOnSuccess(): void
    {
        $vendor = new Vendor('Vendor Example');
        $apiKey = new VendorApiKey($vendor, hash('sha256', 'plain-token'), 'read,write');

        $this->repository
            ->expects(self::exactly(2))
            ->method('findActiveByToken')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);
        $this->entityManager->expects(self::once())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);

        self::assertNull($service->validateToken('plain-token', 'admin'));
        $resolvedVendor = $service->validateToken('plain-token', 'read');

        self::assertSame($vendor, $resolvedVendor);
        self::assertNotNull($apiKey->getLastUsedAt());
    }

    public function testResolveVendorFromAuthHeaderSupportsBearerPrefixAndRejectsBlankHeader(): void
    {
        $vendor = new Vendor('Vendor Example');
        $apiKey = new VendorApiKey($vendor, hash('sha256', 'plain-token'), 'read');

        $this->repository
            ->expects(self::once())
            ->method('findActiveByToken')
            ->with(hash('sha256', 'plain-token'))
            ->willReturn($apiKey);
        $this->entityManager->expects(self::once())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);

        self::assertNull($service->resolveVendorFromAuthHeader('   '));
        self::assertSame($vendor, $service->resolveVendorFromAuthHeader('  Bearer plain-token  '));
    }

    public function testRevokeKeyDeactivatesAndPersistsImmediately(): void
    {
        $apiKey = new VendorApiKey(new Vendor('Vendor Example'), hash('sha256', 'plain-token'), 'read');

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function (VendorApiKey $saved): bool {
                self::assertSame('inactive', $saved->getStatus());

                return true;
            }), true);
        $this->entityManager->expects(self::never())->method('flush');

        (new VendorApiKeyService($this->entityManager, $this->repository))->revokeKey($apiKey);
    }
}
