<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\Entity\VendorTransaction;
use App\Event\VendorTransactionEvent;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\Policy\VendorTransactionAmountPolicyInterface;
use App\ServiceInterface\Policy\VendorTransactionStatusPolicyInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;
use App\ValueObject\VendorTransactionErrorCode;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorTransactionManager implements VendorTransactionManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly VendorTransactionStatusPolicyInterface $statusPolicy,
        private readonly VendorTransactionAmountPolicyInterface $amountPolicy,
        private readonly VendorTransactionRepositoryInterface $transactions,
    ) {
    }

    public function createTransaction(VendorTransactionData $data): VendorTransaction
    {
        $vendorId = $this->normalizeRequiredIdentity($data->vendorId, VendorTransactionErrorCode::VENDOR_ID_REQUIRED);
        $orderId = $this->normalizeRequiredIdentity($data->orderId, VendorTransactionErrorCode::ORDER_ID_REQUIRED);
        $projectId = $this->normalizeProjectId($data->projectId);

        if ($this->transactions->existsForVendorOrderProject($vendorId, $orderId, $projectId)) {
            throw new \InvalidArgumentException(VendorTransactionErrorCode::DUPLICATE_TRANSACTION);
        }

        $tx = new VendorTransaction(
            vendorId: $vendorId,
            orderId: $orderId,
            projectId: $projectId,
            amount: $this->amountPolicy->normalize($data->amount)
        );

        $this->em->persist($tx);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new \InvalidArgumentException(VendorTransactionErrorCode::DUPLICATE_TRANSACTION, 0, $exception);
        }

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::NAME);

        return $tx;
    }

    public function updateStatus(VendorTransaction $tx, string $status): VendorTransaction
    {
        $normalizedStatus = $this->statusPolicy->normalize($status);

        if (!$this->statusPolicy->canTransition($tx->getStatus(), $normalizedStatus)) {
            throw new \InvalidArgumentException(VendorTransactionErrorCode::INVALID_STATUS_TRANSITION);
        }

        $tx->setStatus($normalizedStatus);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorTransactionEvent($tx), VendorTransactionEvent::NAME);

        return $tx;
    }

    private function normalizeRequiredIdentity(string $value, string $message): string
    {
        $normalized = trim($value);

        if ('' == $normalized) {
            throw new \InvalidArgumentException($message);
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
