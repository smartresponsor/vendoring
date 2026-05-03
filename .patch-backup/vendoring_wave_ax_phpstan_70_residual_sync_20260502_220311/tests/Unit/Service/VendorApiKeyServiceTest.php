<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use App\Vendoring\Service\Security\VendorApiKeyService;
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
        $vendor = new VendorEntity('Vendor Example');
        $savedKey = null;

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function (VendorApiKey $apiKey) use ($vendor, &$savedKey): bool {
                $savedKey = $apiKey;

                self::assertSame($vendor, $apiKey->getVendor());
                self::assertSame('read,write', $apiKey->getPermissions());
                self::assertSame('active', $apiKey->getStatus());

                return true;
            }), true);
        $this->entityManager->expects(self::never())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);
        $plainToken = $service->createKey($vendor, 'read,write');

        self::assertSame(64, strlen($plainToken));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $plainToken);
        self::assertInstanceOf(VendorApiKey::class, $savedKey);
        self::assertSame(hash('sha256', $plainToken), $savedKey->getTokenHash());
    }

    public function testRotateKeyDeactivatesExistingKeyAndSavesReplacement(): void
    {
        $vendor = new VendorEntity('Vendor Example');
        $existingKey = new VendorApiKeyEntity($vendor, hash('sha256', 'old-token'), 'read,write');
        $savedKeys = [];

        $this->repository
            ->expects(self::exactly(2))
            ->method('save')
            ->willReturnCallback(function (VendorApiKey $apiKey, bool $flush = false) use (&$savedKeys): void {
                $savedKeys[] = [$apiKey, $flush];
            });
        $this->entityManager->expects(self::once())->method('flush');

        $service = new VendorApiKeyService($this->entityManager, $this->repository);
        $newPlainToken = $service->rotateKey($existingKey);

        self::assertSame(64, strlen($newPlainToken));
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $newPlainToken);
        self::assertCount(2, $savedKeys);
        self::assertSame($existingKey, $savedKeys[0][0]);
        self::assertFalse($savedKeys[0][1]);
        self::assertSame('inactive', $savedKeys[0][0]->getStatus());
        self::assertInstanceOf(VendorApiKey::class, $savedKeys[1][0]);
        self::assertFalse($savedKeys[1][1]);
        self::assertSame($vendor, $savedKeys[1][0]->getVendor());
        self::assertSame($existingKey->getPermissions(), $savedKeys[1][0]->getPermissions());
        self::assertSame('active', $savedKeys[1][0]->getStatus());
        self::assertSame(hash('sha256', $newPlainToken), $savedKeys[1][0]->getTokenHash());
    }

    public function testValidateTokenRespectsPermissionGateAndTouchesActiveKeyOnSuccess(): void
    {
        $vendor = new VendorEntity('Vendor Example');
        $apiKey = new VendorApiKeyEntity($vendor, hash('sha256', 'plain-token'), 'read,write');

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
        $vendor = new VendorEntity('Vendor Example');
        $apiKey = new VendorApiKeyEntity($vendor, hash('sha256', 'plain-token'), 'read');

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
        $apiKey = new VendorApiKeyEntity(new VendorEntity('Vendor Example'), hash('sha256', 'plain-token'), 'read');

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
