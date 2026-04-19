<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Service\VendorTransactionManager;
use App\Vendoring\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionAmountPolicyInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionStatusPolicyInterface;
use App\Vendoring\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\Vendoring\ValueObject\VendorTransactionData;
use App\Vendoring\ValueObject\VendorTransactionErrorCode;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EventDispatcherInterface&MockObject $dispatcher;
    private VendorTransactionStatusPolicyInterface&MockObject $statusPolicy;
    private VendorTransactionAmountPolicyInterface&MockObject $amountPolicy;
    private VendorTransactionRepositoryInterface&MockObject $transactions;
    private RuntimeLoggerInterface&MockObject $runtimeLogger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->statusPolicy = $this->createMock(VendorTransactionStatusPolicyInterface::class);
        $this->amountPolicy = $this->createMock(VendorTransactionAmountPolicyInterface::class);
        $this->transactions = $this->createMock(VendorTransactionRepositoryInterface::class);
        $this->runtimeLogger = $this->createMock(RuntimeLoggerInterface::class);
    }

    public function testCreateTransactionRejectsDuplicateBeforePersistenceWhenBusinessKeyAlreadyExists(): void
    {
        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', 'project-1')
            ->willReturn(true);

        $this->amountPolicy->expects(self::never())->method('normalize');
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');
        $this->runtimeLogger->expects(self::once())->method('warning');
        $this->runtimeLogger->expects(self::never())->method('info');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(VendorTransactionErrorCode::DUPLICATE_TRANSACTION);

        $this->buildManager()->createTransaction(new VendorTransactionData(
            vendorId: 'vendor-1',
            orderId: 'order-1',
            projectId: 'project-1',
            amount: '10.00',
        ));
    }

    public function testCreateTransactionNormalizesDatabaseUniqueViolationToStableDuplicateError(): void
    {
        $this->transactions
            ->expects(self::once())
            ->method('existsForVendorOrderProject')
            ->with('vendor-1', 'order-1', 'project-1')
            ->willReturn(false);

        $this->amountPolicy
            ->expects(self::once())
            ->method('normalize')
            ->with('10.00')
            ->willReturn('10.00');

        $this->entityManager->expects(self::once())->method('persist');
        $this->entityManager
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($this->newUniqueConstraintViolationException());

        $this->dispatcher->expects(self::never())->method('dispatch');
        $this->runtimeLogger->expects(self::once())->method('warning');
        $this->runtimeLogger->expects(self::never())->method('info');

        try {
            $this->buildManager()->createTransaction(new VendorTransactionData(
                vendorId: 'vendor-1',
                orderId: 'order-1',
                projectId: 'project-1',
                amount: '10.00',
            ));

            self::fail('Expected duplicate transaction error.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(VendorTransactionErrorCode::DUPLICATE_TRANSACTION, $exception->getMessage());
            self::assertInstanceOf(UniqueConstraintViolationException::class, $exception->getPrevious());
        }
    }

    private function buildManager(): VendorTransactionManager
    {
        return new VendorTransactionManager(
            $this->entityManager,
            $this->dispatcher,
            $this->statusPolicy,
            $this->amountPolicy,
            $this->transactions,
            $this->runtimeLogger,
        );
    }

    private function newUniqueConstraintViolationException(): UniqueConstraintViolationException
    {
        $reflection = new \ReflectionClass(UniqueConstraintViolationException::class);

        /** @var UniqueConstraintViolationException $exception */
        $exception = $reflection->newInstanceWithoutConstructor();

        return $exception;
    }
}
