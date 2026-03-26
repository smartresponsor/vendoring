<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorAnalytics;
use App\Entity\Vendor\VendorApiKey;
use App\Entity\Vendor\VendorAttachment;
use App\Entity\Vendor\VendorBilling;
use App\Entity\Vendor\VendorDocument;
use App\Entity\Vendor\VendorLedgerBinding;
use App\Entity\Vendor\VendorMedia;
use App\Entity\Vendor\VendorPassport;
use App\Entity\Vendor\VendorProfile;
use App\Entity\Vendor\VendorSecurity;
use App\Entity\Vendor\VendorTransaction;
use App\Repository\VendorAnalyticsRepository;
use App\Repository\VendorApiKeyRepository;
use App\Repository\VendorAttachmentRepository;
use App\Repository\VendorBillingRepository;
use App\Repository\VendorDocumentRepository;
use App\Repository\VendorLedgerBindingRepository;
use App\Repository\VendorMediaRepository;
use App\Repository\VendorPassportRepository;
use App\Repository\VendorProfileRepository;
use App\Repository\VendorRepository;
use App\Repository\VendorSecurityRepository;
use App\Repository\VendorTransactionRepository;
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
        yield 'vendor' => [VendorRepository::class, Vendor::class];
        yield 'vendor_analytics' => [VendorAnalyticsRepository::class, VendorAnalytics::class];
        yield 'vendor_api_key' => [VendorApiKeyRepository::class, VendorApiKey::class];
        yield 'vendor_attachment' => [VendorAttachmentRepository::class, VendorAttachment::class];
        yield 'vendor_billing' => [VendorBillingRepository::class, VendorBilling::class];
        yield 'vendor_document' => [VendorDocumentRepository::class, VendorDocument::class];
        yield 'vendor_ledger_binding' => [VendorLedgerBindingRepository::class, VendorLedgerBinding::class];
        yield 'vendor_media' => [VendorMediaRepository::class, VendorMedia::class];
        yield 'vendor_passport' => [VendorPassportRepository::class, VendorPassport::class];
        yield 'vendor_profile' => [VendorProfileRepository::class, VendorProfile::class];
        yield 'vendor_security' => [VendorSecurityRepository::class, VendorSecurity::class];
        yield 'vendor_transaction' => [VendorTransactionRepository::class, VendorTransaction::class];
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
