<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Service\Transaction\VendorTransactionLifecycleService;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionAmountPolicyServiceInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionStatusPolicyServiceInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionLifecycleServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EventDispatcherInterface&MockObject $dispatcher;
    private VendorTransactionStatusPolicyServiceInterface&MockObject $statusPolicy;
    private VendorTransactionAmountPolicyServiceInterface&MockObject $amountPolicy;
    private VendorTransactionRepositoryInterface&MockObject $transactions;
    private VendorRuntimeLoggerServiceInterface&MockObject $runtimeLogger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->statusPolicy = $this->createMock(VendorTransactionStatusPolicyServiceInterface::class);
        $this->amountPolicy = $this->createMock(VendorTransactionAmountPolicyServiceInterface::class);
        $this->transactions = $this->createMock(VendorTransactionRepositoryInterface::class);
        $this->runtimeLogger = $this->createMock(VendorRuntimeLoggerServiceInterface::class);
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
        $this->expectExceptionMessage(VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION);

        $this->buildManager()->createTransaction(new VendorTransactionDataValueObject(
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
            $this->buildManager()->createTransaction(new VendorTransactionDataValueObject(
                vendorId: 'vendor-1',
                orderId: 'order-1',
                projectId: 'project-1',
                amount: '10.00',
            ));

            self::fail('Expected duplicate transaction error.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame(VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION, $exception->getMessage());
            self::assertInstanceOf(UniqueConstraintViolationException::class, $exception->getPrevious());
        }
    }

    private function buildManager(): VendorTransactionLifecycleService
    {
        return new VendorTransactionLifecycleService(
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
