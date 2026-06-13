<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorAnalyticsEntity;
use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorPassportEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\Entity\Vendor\VendorSecurityEntity;
use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\Repository\Vendor\VendorAnalyticsRepository;
use App\Vendoring\Repository\Vendor\VendorApiKeyRepository;
use App\Vendoring\Repository\Vendor\VendorAttachmentRepository;
use App\Vendoring\Repository\Vendor\VendorBillingRepository;
use App\Vendoring\Repository\Vendor\VendorDocumentRepository;
use App\Vendoring\Repository\Vendor\VendorLedgerBindingRepository;
use App\Vendoring\Repository\Vendor\VendorMediaRepository;
use App\Vendoring\Repository\Vendor\VendorPassportRepository;
use App\Vendoring\Repository\Vendor\VendorProfileRepository;
use App\Vendoring\Repository\Vendor\VendorRepository;
use App\Vendoring\Repository\Vendor\VendorSecurityRepository;
use App\Vendoring\Repository\Vendor\VendorTransactionRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DoctrineRepositoryContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, class-string}>
     */
    public static function repositoryMapProvider(): iterable
    {
        yield 'vendor' => [VendorRepository::class, VendorEntity::class];
        yield 'vendor_analytics' => [VendorAnalyticsRepository::class, VendorAnalyticsEntity::class];
        yield 'vendor_api_key' => [VendorApiKeyRepository::class, VendorApiKeyEntity::class];
        yield 'vendor_attachment' => [VendorAttachmentRepository::class, VendorAttachmentEntity::class];
        yield 'vendor_billing' => [VendorBillingRepository::class, VendorBillingEntity::class];
        yield 'vendor_document' => [VendorDocumentRepository::class, VendorDocumentEntity::class];
        yield 'vendor_ledger_binding' => [VendorLedgerBindingRepository::class, VendorLedgerBindingEntity::class];
        yield 'vendor_media' => [VendorMediaRepository::class, VendorMediaEntity::class];
        yield 'vendor_passport' => [VendorPassportRepository::class, VendorPassportEntity::class];
        yield 'vendor_profile' => [VendorProfileRepository::class, VendorProfileEntity::class];
        yield 'vendor_security' => [VendorSecurityRepository::class, VendorSecurityEntity::class];
        yield 'vendor_transaction' => [VendorTransactionRepository::class, VendorTransactionEntity::class];
    }

    #[DataProvider('repositoryMapProvider')]
    public function testDoctrineRepositoriesExtendCanonicalBase(string $repositoryClass, string $entityClass): void
    {
        self::assertTrue(is_a($repositoryClass, ServiceEntityRepository::class, true));
        self::assertTrue(class_exists($entityClass));
    }

    #[DataProvider('repositoryMapProvider')]
    public function testDoctrineRepositoriesAcceptManagerRegistry(string $repositoryClass, string $entityClass): void
    {
        self::assertTrue(class_exists($repositoryClass));
        /** @var class-string $repositoryClass */
        $reflection = new \ReflectionClass($repositoryClass);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());

        $parameter = $constructor->getParameters()[0];
        $type = $parameter->getType();

        self::assertNotNull($type);
        self::assertSame(ManagerRegistry::class, $type instanceof \ReflectionNamedType ? $type->getName() : null);
    }
}
