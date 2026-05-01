<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorAnalytics;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\Entity\VendorAttachment;
use App\Vendoring\Entity\VendorBilling;
use App\Vendoring\Entity\VendorDocument;
use App\Vendoring\Entity\VendorLedgerBinding;
use App\Vendoring\Entity\VendorMedia;
use App\Vendoring\Entity\VendorPassport;
use App\Vendoring\Entity\VendorProfile;
use App\Vendoring\Entity\VendorSecurity;
use App\Vendoring\Entity\VendorTransaction;
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
