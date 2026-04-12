<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\Entity\VendorTransaction;
use App\Event\VendorTransactionEvent;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use App\ServiceInterface\Policy\VendorTransactionAmountPolicyInterface;
use App\ServiceInterface\Policy\VendorTransactionStatusPolicyInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;
use App\ValueObject\VendorTransactionErrorCode;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorTransactionManager implements VendorTransactionManagerInterface
{
    // Stable validation surface: duplicate_transaction.
    public function __construct(
        private EntityManagerInterface                 $em,
        private EventDispatcherInterface               $dispatcher,
        private VendorTransactionStatusPolicyInterface $statusPolicy,
        private VendorTransactionAmountPolicyInterface $amountPolicy,
        private VendorTransactionRepositoryInterface   $transactions,
        private RuntimeLoggerInterface                 $runtimeLogger,
    ) {}

    public function createTransaction(VendorTransactionData $data): VendorTransaction
    {
        $vendorId = $this->normalizeRequiredIdentity($data->vendorId, VendorTransactionErrorCode::VENDOR_ID_REQUIRED);
        $orderId = $this->normalizeRequiredIdentity($data->orderId, VendorTransactionErrorCode::ORDER_ID_REQUIRED);
        $projectId = $this->normalizeProjectId($data->projectId);

        if ($this->transactions->existsForVendorOrderProject($vendorId, $orderId, $projectId)) {
            $this->runtimeLogger->warning('vendor_transaction_duplicate_rejected', [
                'vendor_id' => $vendorId,
                'order_id' => $orderId,
                'project_id' => $projectId,
                'error_code' => VendorTransactionErrorCode::DUPLICATE_TRANSACTION,
            ]);

            throw new InvalidArgumentException(VendorTransactionErrorCode::DUPLICATE_TRANSACTION);
        }

        $tx = new VendorTransaction(
            vendorId: $vendorId,
            orderId: $orderId,
            projectId: $projectId,
            amount: $this->amountPolicy->normalize($data->amount),
        );

        $this->em->persist($tx);
        $this->em->flush();

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

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction
    {
        $normalizedStatus = $this->statusPolicy->normalize($status);

        if (!$this->statusPolicy->canTransition($tx->getStatus(), $normalizedStatus)) {
            $this->runtimeLogger->warning('vendor_transaction_status_transition_rejected', [
                'vendor_id' => $tx->getVendorId(),
                'transaction_id' => null !== $tx->getId() ? (string) $tx->getId() : null,
                'from_status' => $tx->getStatus(),
                'to_status' => $normalizedStatus,
                'error_code' => VendorTransactionErrorCode::INVALID_STATUS_TRANSITION,
            ]);

            throw new InvalidArgumentException(VendorTransactionErrorCode::INVALID_STATUS_TRANSITION);
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
