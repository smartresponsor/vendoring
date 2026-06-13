<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Transaction;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;

final class InMemoryVendorTransactionRepository implements VendorTransactionRepositoryInterface
{
    /** @var list<VendorTransactionEntity> */
    private array $items;

    /** @param list<VendorTransactionEntity> $items */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function findByVendorId(string $vendorId): array
    {
        return array_values(array_filter(
            $this->items,
            static fn(VendorTransactionEntity $transaction): bool => $transaction->getVendorId() === $vendorId,
        ));
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransactionEntity
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
