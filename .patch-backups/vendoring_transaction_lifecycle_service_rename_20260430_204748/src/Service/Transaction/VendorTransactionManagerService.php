<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\Event\Vendor\VendorTransactionEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionAmountPolicyServiceInterface;
use App\Vendoring\ServiceInterface\Policy\VendorTransactionStatusPolicyServiceInterface;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionManagerServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Throwable;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorTransactionManagerService implements VendorTransactionManagerServiceInterface
{
    // Stable validation surface: duplicate_transaction.
    public function __construct(
        private EntityManagerInterface                 $em,
        private EventDispatcherInterface               $dispatcher,
        private VendorTransactionStatusPolicyServiceInterface $statusPolicy,
        private VendorTransactionAmountPolicyServiceInterface $amountPolicy,
        private VendorTransactionRepositoryInterface   $transactions,
        private VendorRuntimeLoggerServiceInterface                 $runtimeLogger,
    ) {}

    /**
     * @throws Throwable
     */
    public function createTransaction(VendorTransactionDataValueObject $data): VendorTransactionEntity
    {
        $vendorId = $this->normalizeRequiredIdentity($data->vendorId, VendorTransactionErrorCodeValueObject::VENDOR_ID_REQUIRED);
        $orderId = $this->normalizeRequiredIdentity($data->orderId, VendorTransactionErrorCodeValueObject::ORDER_ID_REQUIRED);
        $projectId = $this->normalizeProjectId($data->projectId);

        if ($this->transactions->existsForVendorOrderProject($vendorId, $orderId, $projectId)) {
            $this->runtimeLogger->warning('vendor_transaction_duplicate_rejected', [
                'vendor_id' => $vendorId,
                'order_id' => $orderId,
                'project_id' => $projectId,
                'error_code' => VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION,
            ]);

            throw new InvalidArgumentException(VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION);
        }

        $tx = new VendorTransactionEntity(
            vendorId: $vendorId,
            orderId: $orderId,
            projectId: $projectId,
            amount: $this->amountPolicy->normalize($data->amount),
        );

        $this->em->persist($tx);

        try {
            $this->em->flush();
        } catch (Throwable $exception) {
            if (!$exception instanceof UniqueConstraintViolationException) {
                throw $exception;
            }

            $this->runtimeLogger->warning('vendor_transaction_duplicate_rejected', [
                'vendor_id' => $vendorId,
                'order_id' => $orderId,
                'project_id' => $projectId,
                'error_code' => VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION,
            ]);

            throw new InvalidArgumentException(VendorTransactionErrorCodeValueObject::DUPLICATE_TRANSACTION, previous: $exception);
        }

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::EVENT_NAME);
        $this->runtimeLogger->info('vendor_transaction_created', [
            'vendor_id' => $tx->getVendorId(),
            'transaction_id' => null !== $tx->getId() ? (string) $tx->getId() : null,
            'order_id' => $tx->getOrderId(),
            'project_id' => $tx->getProjectId(),
            'status' => $tx->getStatus(),
        ]);

        return $tx;
    }

    public function updateStatus(VendorTransactionEntity $tx, string $status): VendorTransactionEntity
    {
        $normalizedStatus = $this->statusPolicy->normalize($status);

        if (!$this->statusPolicy->canTransition($tx->getStatus(), $normalizedStatus)) {
            $this->runtimeLogger->warning('vendor_transaction_status_transition_rejected', [
                'vendor_id' => $tx->getVendorId(),
                'transaction_id' => null !== $tx->getId() ? (string) $tx->getId() : null,
                'from_status' => $tx->getStatus(),
                'to_status' => $normalizedStatus,
                'error_code' => VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION,
            ]);

            throw new InvalidArgumentException(VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION);
        }

        $tx->setStatus($normalizedStatus);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::EVENT_NAME);
        $this->runtimeLogger->info('vendor_transaction_status_updated', [
            'vendor_id' => $tx->getVendorId(),
            'transaction_id' => null !== $tx->getId() ? (string) $tx->getId() : null,
            'status' => $tx->getStatus(),
        ]);

        return $tx;
    }

    private function normalizeRequiredIdentity(string $value, string $message): string
    {
        $normalized = trim($value);

        if ('' == $normalized) {
            throw new InvalidArgumentException($message);
        }

        return $normalized;
    }

    private function normalizeProjectId(?string $projectId): ?string
    {
        if (null === $projectId) {
            return null;
        }

        $normalized = trim($projectId);

        return '' === $normalized ? null : $normalized;
    }
}
