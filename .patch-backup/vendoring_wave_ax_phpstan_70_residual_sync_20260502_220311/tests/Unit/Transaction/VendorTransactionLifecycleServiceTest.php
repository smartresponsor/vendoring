<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Transaction;

use App\Vendoring\Entity\VendorTransaction;
use App\Vendoring\Event\Vendor\VendorTransactionEvent;
use App\Vendoring\Service\Observability\VendorCorrelationContextService;
use App\Vendoring\Service\Observability\VendorRuntimeLoggerService;
use App\Vendoring\Service\Runtime\VendorAppEnvResolverService;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use App\Vendoring\Service\Policy\VendorTransactionAmountPolicyService;
use App\Vendoring\Service\Policy\VendorTransactionStatusPolicyService;
use App\Vendoring\Service\Transaction\VendorTransactionLifecycleService;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionLifecycleServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private EventDispatcherInterface&MockObject $dispatcher;

    private VendorTransactionRepositoryInterface&MockObject $transactions;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->transactions = $this->createMock(VendorTransactionRepositoryInterface::class);
    }

    public function testCreateTransactionPersistsFlushesAndDispatchesEvent(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('vendor-1', 'order-1', 'project-1', '10.50');

        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', 'project-1')
            ->willReturn(false);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static function (VendorTransaction $transaction): bool {
                return 'vendor-1' === $transaction->getVendorId()
                    && 'order-1' === $transaction->getOrderId()
                    && 'project-1' === $transaction->getProjectId()
                    && '10.50' === $transaction->getAmount()
                    && 'pending' === $transaction->getStatus();
            }));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static function (VendorTransactionEvent $event): bool {
                    return 'vendor-1' === $event->transaction->getVendorId();
                }),
                VendorTransactionEvent::NAME,
            )
            ->willReturnArgument(0);

        $transaction = $manager->createTransaction($data);

        self::assertSame('vendor-1', $transaction->getVendorId());
        self::assertSame('order-1', $transaction->getOrderId());
        self::assertSame('project-1', $transaction->getProjectId());
        self::assertSame('10.50', $transaction->getAmount());
        self::assertSame('pending', $transaction->getStatus());
    }

    public function testCreateTransactionNormalizesBlankProjectIdToNullBeforeDuplicateCheck(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('vendor-1', 'order-1', '   ', '10.50');

        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', null)
            ->willReturn(false);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static function (VendorTransaction $transaction): bool {
                return null === $transaction->getProjectId();
            }));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->willReturnArgument(0);

        $transaction = $manager->createTransaction($data);

        self::assertNull($transaction->getProjectId());
    }

    public function testCreateTransactionRejectsDuplicateWithoutPersisting(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('vendor-1', 'order-1', null, '10.50');

        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', null)
            ->willReturn(true);

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->dispatcher
            ->expects(self::never())
            ->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate_transaction');

        $manager->createTransaction($data);
    }

    public function testCreateTransactionNormalizesVendorAndOrderBeforeDuplicateCheck(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('  vendor-1  ', '  order-1  ', 'project-1', '10.50');

        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', 'project-1')
            ->willReturn(false);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static function (VendorTransaction $transaction): bool {
                return 'vendor-1' === $transaction->getVendorId()
                    && 'order-1' === $transaction->getOrderId();
            }));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->willReturnArgument(0);

        $transaction = $manager->createTransaction($data);

        self::assertSame('vendor-1', $transaction->getVendorId());
        self::assertSame('order-1', $transaction->getOrderId());
    }

    public function testCreateTransactionRejectsBlankVendorIdAfterTrimWithoutPersisting(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('   ', 'order-1', null, '10.50');

        $this->transactions
            ->expects(self::never())
            ->method('existsForVendorOrderProject');

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->dispatcher
            ->expects(self::never())
            ->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('vendor_id_required');

        $manager->createTransaction($data);
    }

    public function testCreateTransactionRejectsInvalidAmountWithoutPersisting(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $data = new VendorTransactionDataValueObject('vendor-1', 'order-1', 'project-1', '0');

        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', 'project-1')
            ->willReturn(false);

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->dispatcher
            ->expects(self::never())
            ->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount_not_positive');

        $manager->createTransaction($data);
    }

    public function testUpdateStatusFlushesAndDispatchesWhenTransitionIsAllowed(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.50');

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(
                self::callback(static function (object $event) use ($transaction): bool {
                    return $event instanceof \App\Vendoring\Event\Vendor\VendorTransactionEvent
                        && $event->transaction === $transaction
                        && 'authorized' === $event->transaction->getStatus();
                }),
                VendorTransactionEvent::NAME,
            )
            ->willReturnArgument(0);

        $updated = $manager->updateStatus($transaction, ' AUTHORIZED ');

        self::assertSame($transaction, $updated);
        self::assertSame('authorized', $updated->getStatus());
    }

    public function testUpdateStatusRejectsInvalidTransitionWithoutFlushing(): void
    {
        $manager = new VendorTransactionLifecycleService(
            $this->entityManager,
            $this->dispatcher,
            new VendorTransactionStatusPolicyService(),
            new VendorTransactionAmountPolicyService(),
            $this->transactions,
            $this->runtimeLogger(),
        );

        $transaction = new VendorTransaction('vendor-1', 'order-1', null, '10.50');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $this->dispatcher
            ->expects(self::never())
            ->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid_status_transition');

        $manager->updateStatus($transaction, ' refunded ');
    }

    private function runtimeLogger(): VendorRuntimeLoggerService
    {
        return new VendorRuntimeLoggerService(new VendorCorrelationContextService(), new RequestStack(), new VendorAppEnvResolverService());
    }
}
