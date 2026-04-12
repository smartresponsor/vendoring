<?php

declare(strict_types=1);

namespace App\Tests\Support\Transaction;

use App\Entity\VendorTransaction;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;

final class InMemoryVendorTransactionRepository implements VendorTransactionRepositoryInterface
{
    /** @var list<VendorTransaction> */
    private array $items;

    /** @param list<VendorTransaction> $items */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function findByVendorId(string $vendorId): array
    {
        return array_values(array_filter(
            $this->items,
            static fn(VendorTransaction $transaction): bool => $transaction->getVendorId() === $vendorId,
        ));
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction
    {
        foreach ($this->items as $transaction) {
            if ($transaction->getId() === $id && $transaction->getVendorId() === $vendorId) {
                return $transaction;
            }
        }

        return null;
    }

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        foreach ($this->items as $transaction) {
            if (
                $transaction->getVendorId() === $vendorId
                && $transaction->getOrderId() === $orderId
                && $transaction->getProjectId() === $projectId
            ) {
                return true;
            }
        }

        return false;
    }
}
