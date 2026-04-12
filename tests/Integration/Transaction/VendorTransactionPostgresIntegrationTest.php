<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Integration\Transaction;

use App\Entity\Vendor\VendorTransaction;
use App\Service\Policy\VendorTransactionAmountPolicy;
use App\Service\Policy\VendorTransactionStatusPolicy;
use App\Service\VendorTransactionManager;
use App\Tests\Support\Transaction\DoctrineBackedVendorTransactionRepository;
use App\Tests\Support\Transaction\DoctrineEntityManagerFactory;
use App\ValueObject\VendorTransactionData;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionPostgresIntegrationTest extends TestCase
{
    public function testPostgresFlowPersistsReadsUpdatesAndGuardsDuplicates(): void
    {
        if (!extension_loaded('pdo_pgsql')) {
            self::markTestSkipped('pdo_pgsql is required for postgres integration test');
        }

        $dsn = (string) ($_ENV['VENDOR_TEST_POSTGRES_DSN'] ?? $_SERVER['VENDOR_TEST_POSTGRES_DSN'] ?? '');
        if ('' === trim($dsn)) {
            self::markTestSkipped('Set VENDOR_TEST_POSTGRES_DSN to run postgres integration test');
        }

        $projectRoot = dirname(__DIR__, 3);
        $entityManager = DoctrineEntityManagerFactory::createPostgresEntityManager($projectRoot, $dsn);
        $schemaTool = new SchemaTool($entityManager);
        $metadata = [$entityManager->getClassMetadata(VendorTransaction::class)];

        try {
            $schemaTool->dropSchema($metadata);
        } catch (\Throwable) {
        }

        $schemaTool->createSchema($metadata);

        $events = new class implements EventDispatcherInterface {
            public int $dispatchCount = 0;

            public function dispatch(object $event, ?string $eventName = null): object
            {
                ++$this->dispatchCount;

                return $event;
            }
        };

        $repository = new DoctrineBackedVendorTransactionRepository($entityManager);
        $manager = new VendorTransactionManager(
            $entityManager,
            $events,
            new VendorTransactionStatusPolicy(),
            new VendorTransactionAmountPolicy(),
            $repository,
        );

        $created = $manager->createTransaction(new VendorTransactionData('vendor-pg-1', 'order-pg-1', null, '11.50'));
        self::assertNotNull($created->getId());
        self::assertSame('pending', $created->getStatus());
        self::assertSame(1, $events->dispatchCount);

        $fetched = $repository->findOneByIdAndVendorId((int) $created->getId(), 'vendor-pg-1');
        self::assertInstanceOf(VendorTransaction::class, $fetched);
        self::assertSame('11.50', $fetched->getAmount());
        self::assertTrue($repository->existsForVendorOrderProject('vendor-pg-1', 'order-pg-1', null));

        $updated = $manager->updateStatus($created, 'authorized');
        self::assertSame('authorized', $updated->getStatus());
        self::assertSame(2, $events->dispatchCount);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate_transaction');
        $manager->createTransaction(new VendorTransactionData('vendor-pg-1', 'order-pg-1', null, '11.50'));
    }
}
