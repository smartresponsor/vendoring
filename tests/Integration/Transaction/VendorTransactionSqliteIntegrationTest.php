<?php

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

final class VendorTransactionSqliteIntegrationTest extends TestCase
{
    public function testSqliteDoctrineFlowPersistsReadsUpdatesAndGuardsDuplicates(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            self::markTestSkipped('pdo_sqlite is required for sqlite integration test');
        }

        $projectRoot = dirname(__DIR__, 3);
        $entityManager = DoctrineEntityManagerFactory::createSqliteMemoryEntityManager($projectRoot);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = [$entityManager->getClassMetadata(VendorTransaction::class)];
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

        $created = $manager->createTransaction(new VendorTransactionData('vendor-1', 'order-1', null, '10.50'));
        self::assertNotNull($created->getId());
        self::assertSame('pending', $created->getStatus());
        self::assertSame(1, $events->dispatchCount);

        $fetched = $repository->findOneByIdAndVendorId((int) $created->getId(), 'vendor-1');
        self::assertInstanceOf(VendorTransaction::class, $fetched);
        self::assertSame('10.50', $fetched->getAmount());
        self::assertTrue($repository->existsForVendorOrderProject('vendor-1', 'order-1', null));

        $updated = $manager->updateStatus($created, 'authorized');
        self::assertSame('authorized', $updated->getStatus());
        self::assertSame(2, $events->dispatchCount);

        $reloaded = $repository->findOneByIdAndVendorId((int) $created->getId(), 'vendor-1');
        self::assertInstanceOf(VendorTransaction::class, $reloaded);
        self::assertSame('authorized', $reloaded->getStatus());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate_transaction');
        $manager->createTransaction(new VendorTransactionData('vendor-1', 'order-1', null, '10.50'));
    }
}
