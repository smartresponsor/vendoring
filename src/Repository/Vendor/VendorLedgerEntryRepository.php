<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerEntryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorLedgerEntryRepository implements VendorLedgerEntryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function insert(VendorLedgerEntryEntity $entry): void
    {
        $this->entityManager->persist($entry);
        $this->entityManager->flush();
    }

    /**
     * @return list<VendorLedgerEntryEntity>
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $criteria = [
            'tenantId' => $tenantId,
            'referenceType' => $referenceType,
            'referenceId' => $referenceId,
        ];

        if (null !== $vendorId) {
            $criteria['vendorId'] = $vendorId;
        }

        /** @var list<VendorLedgerEntryEntity> $entries */
        $entries = $this->entityManager->getRepository(VendorLedgerEntryEntity::class)->findBy($criteria, ['createdAt' => 'ASC', 'id' => 'ASC']);

        return $entries;
    }

    public function sumByAccount(VendorLedgerAccountSumCriteriaDTO $criteria): float
    {
        $entries = $this->matchingEntries($criteria);
        $total = 0.0;

        foreach ($entries as $entry) {
            if ($entry->debitAccount === $criteria->accountCode) {
                $total += $entry->amount;
            }

            if ($entry->creditAccount === $criteria->accountCode) {
                $total -= $entry->amount;
            }
        }

        return $total;
    }

    /**
     * @return list<object{currency:string,balanceCents:int}>
     */
    public function balancesForVendor(string $vendorId): array
    {
        /** @var list<VendorLedgerEntryEntity> $entries */
        $entries = $this->entityManager->getRepository(VendorLedgerEntryEntity::class)->findBy(['vendorId' => $vendorId], ['createdAt' => 'ASC', 'id' => 'ASC']);
        $balances = [];

        foreach ($entries as $entry) {
            $currency = strtoupper($entry->currency);
            $balances[$currency] ??= 0;
            $balances[$currency] += (int) round($entry->amount * 100);
        }

        $result = [];
        foreach ($balances as $currency => $balanceCents) {
            $result[] = (object) [
                'currency' => $currency,
                'balanceCents' => $balanceCents,
            ];
        }

        return $result;
    }

    /**
     * @return list<VendorLedgerEntryEntity>
     */
    private function matchingEntries(VendorLedgerAccountSumCriteriaDTO $criteria): array
    {
        $criteriaMap = ['tenantId' => $criteria->tenantId];

        if (null !== $criteria->currency) {
            $criteriaMap['currency'] = $criteria->currency;
        }

        if (null !== $criteria->vendorId) {
            $criteriaMap['vendorId'] = $criteria->vendorId;
        }

        /** @var list<VendorLedgerEntryEntity> $entries */
        $entries = $this->entityManager->getRepository(VendorLedgerEntryEntity::class)->findBy($criteriaMap, ['createdAt' => 'ASC', 'id' => 'ASC']);

        return array_values(array_filter(
            $entries,
            static function (VendorLedgerEntryEntity $entry) use ($criteria): bool {
                if (null !== $criteria->from && $entry->createdAt < $criteria->from) {
                    return false;
                }

                if (null !== $criteria->to && $entry->createdAt > $criteria->to) {
                    return false;
                }

                return $entry->debitAccount === $criteria->accountCode || $entry->creditAccount === $criteria->accountCode;
            },
        ));
    }
}
