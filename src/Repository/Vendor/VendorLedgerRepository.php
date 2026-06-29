<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorLedgerRepository extends ServiceEntityRepository implements VendorLedgerRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorLedgerEntity::class);
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function insert(VendorLedgerEntity $entry): void
    {
        $this->getEntityManager()->persist($entry);
    }

    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $queryBuilder = $this->createQueryBuilder('ledger')
            ->andWhere('ledger.tenantId = :tenantId')
            ->andWhere('ledger.referenceType = :referenceType')
            ->andWhere('ledger.referenceId = :referenceId')
            ->setParameter('tenantId', $tenantId)
            ->setParameter('referenceType', $referenceType)
            ->setParameter('referenceId', $referenceId);

        if (null !== $vendorId) {
            $queryBuilder
                ->andWhere('ledger.vendorId = :vendorId')
                ->setParameter('vendorId', $vendorId);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function sumByAccount(VendorLedgerAccountSumCriteriaDTO $criteria): float
    {
        $sum = 0.0;

        foreach ($this->findBy(['tenantId' => $criteria->tenantId]) as $entry) {
            if (!$entry instanceof VendorLedgerEntity) {
                continue;
            }

            if (null !== $criteria->vendorId && $entry->vendorId !== $criteria->vendorId) {
                continue;
            }

            if (null !== $criteria->currency && $entry->currency !== $criteria->currency) {
                continue;
            }

            if ($entry->debitAccount === $criteria->accountCode) {
                $sum += $entry->amount;
            }

            if ($entry->creditAccount === $criteria->accountCode) {
                $sum -= $entry->amount;
            }
        }

        return $sum;
    }

    public function balancesForVendor(string $vendorId): array
    {
        $balances = [];

        foreach ($this->findBy(['vendorId' => $vendorId]) as $entry) {
            if (!$entry instanceof VendorLedgerEntity) {
                continue;
            }

            $balances[$entry->currency] ??= 0;
            $balances[$entry->currency] += (int) round($entry->amount * 100);
        }

        return array_map(
            static fn (string $currency, int $balanceCents): object => (object) [
                'currency' => $currency,
                'balanceCents' => $balanceCents,
            ],
            array_keys($balances),
            array_values($balances),
        );
    }
}
