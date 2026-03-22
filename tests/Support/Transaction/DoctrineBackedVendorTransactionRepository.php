<?php

declare(strict_types=1);

namespace App\Tests\Support\Transaction;

use App\Entity\Vendor\VendorTransaction;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineBackedVendorTransactionRepository implements VendorTransactionRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function findByVendorId(string $vendorId): array
    {
        /** @var list<VendorTransaction> $transactions */
        $transactions = $this->entityManager
            ->getRepository(VendorTransaction::class)
            ->findBy(['vendorId' => $vendorId], ['createdAt' => 'DESC', 'id' => 'DESC']);

        return $transactions;
    }

    public function findOneByIdAndVendorId(int $id, string $vendorId): ?VendorTransaction
    {
        $transaction = $this->entityManager
            ->getRepository(VendorTransaction::class)
            ->findOneBy(['id' => $id, 'vendorId' => $vendorId]);

        return $transaction instanceof VendorTransaction ? $transaction : null;
    }

    public function existsForVendorOrderProject(string $vendorId, string $orderId, ?string $projectId): bool
    {
        $criteria = [
            'vendorId' => $vendorId,
            'orderId' => $orderId,
            'projectId' => $projectId,
        ];

        return $this->entityManager
            ->getRepository(VendorTransaction::class)
            ->findOneBy($criteria) instanceof VendorTransaction;
    }
}
