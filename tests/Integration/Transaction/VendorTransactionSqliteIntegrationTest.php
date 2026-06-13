<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use App\Vendoring\Service\Runtime\VendorAppEnvResolverService;
use App\Vendoring\Service\Policy\VendorTransactionAmountPolicyService;
use App\Vendoring\Service\Policy\VendorTransactionStatusPolicyService;
use App\Vendoring\Service\Transaction\VendorTransactionLifecycleService;
use App\Vendoring\Tests\Support\Transaction\DoctrineBackedVendorTransactionRepository;
use App\Vendoring\Tests\Support\Transaction\DoctrineEntityManagerFactory;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $metadata = [$entityManager->getClassMetadata(VendorTransactionEntity::class)];
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
        $manager = new VendorTransactionLifecycleService(
            $entityManager,
            $events,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $repository,
            new VendorRuntimeLoggerService(new VendorCorrelationContextService(), new RequestStack(), new VendorAppEnvResolverService()),
        );

        $created = $manager->createTransaction(new VendorTransactionDataValueObject('vendor-1', 'order-1', null, '10.50'));
        self::assertNotNull($created->getId());
        self::assertSame('pending', $created->getStatus());
        self::assertSame(1, $events->dispatchCount);

        $fetched = $repository->findOneByIdAndVendorId((int) $created->getId(), 'vendor-1');
        self::assertInstanceOf(VendorTransactionEntity::class, $fetched);
        self::assertSame('10.50', $fetched->getAmount());
        self::assertTrue($repository->existsForVendorOrderProject('vendor-1', 'order-1', null));

        $updated = $manager->updateStatus($created, 'authorized');
        self::assertSame('authorized', $updated->getStatus());
        self::assertSame(2, $events->dispatchCount);

        $reloaded = $repository->findOneByIdAndVendorId((int) $created->getId(), 'vendor-1');
        self::assertInstanceOf(VendorTransactionEntity::class, $reloaded);
        self::assertSame('authorized', $reloaded->getStatus());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate_transaction');
        $manager->createTransaction(new VendorTransactionDataValueObject('vendor-1', 'order-1', null, '10.50'));
    }
}
