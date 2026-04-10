<?php

declare(strict_types=1);

namespace App\Repository\Ledger;

use App\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class LedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /** @throws Exception */
    public function insert(LedgerEntry $entry): void
    {
        $this->connection->insert('vendor_ledger_entries', [
            'tenant_id' => $entry->tenantId,
            'vendor_id' => $entry->vendorId,
            'reference_type' => $entry->referenceType,
            'reference_id' => $entry->referenceId,
            'debit_account' => $entry->debitAccount,
            'credit_account' => $entry->creditAccount,
            'amount' => $entry->amount,
            'currency' => $entry->currency,
            'created_at' => $entry->createdAt,
        ]);
    }

    /**
     * @return list<LedgerEntry>
     * @throws Exception
     */
    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $sql = 'SELECT * FROM vendor_ledger_entries WHERE tenant_id = :tenantId AND reference_type = :referenceType AND reference_id = :referenceId';
        $params = [
            'tenantId' => $tenantId,
            'referenceType' => $referenceType,
            'referenceId' => $referenceId,
        ];

        if (null !== $vendorId) {
            $sql .= ' AND vendor_id = :vendorId';
            $params['vendorId'] = $vendorId;
        }

        $rows = $this->connection->fetchAllAssociative($sql, $params);

        return array_map(
            function (array $row): LedgerEntry {
                $tenantValue = self::stringValue($row['tenant_id'] ?? null);
                $vendorValue = self::nullableStringValue($row['vendor_id'] ?? null);
                $referenceTypeValue = self::stringValue($row['reference_type'] ?? null);
                $referenceIdValue = self::stringValue($row['reference_id'] ?? null);
                $debitAccountValue = self::stringValue($row['debit_account'] ?? null);
                $creditAccountValue = self::stringValue($row['credit_account'] ?? null);
                $currencyValue = self::stringValue($row['currency'] ?? null);
                $createdAtValue = self::stringValue($row['created_at'] ?? null);

                return new LedgerEntry(
                    id: self::stringValue($row['id'] ?? sha1($tenantValue.'|'.$referenceTypeValue.'|'.$referenceIdValue.'|'.$createdAtValue)),
                    tenantId: $tenantValue,
                    debitAccount: $debitAccountValue,
                    creditAccount: $creditAccountValue,
                    amount: self::floatValue($row['amount'] ?? null),
                    currency: $currencyValue,
                    referenceType: $referenceTypeValue,
                    referenceId: $referenceIdValue,
                    vendorId: $vendorValue,
                    createdAt: $createdAtValue,
                );
            },
            $rows,
        );
    }

    /** @throws Exception */
    public function sumByAccount(LedgerAccountSumCriteriaDTO $criteria): float
    {
        $sql = 'SELECT COALESCE(SUM(CASE WHEN debit_account = :account THEN amount WHEN credit_account = :account THEN -amount ELSE 0 END), 0) total FROM vendor_ledger_entries WHERE tenant_id = :tenantId';
        $params = [
            'tenantId' => $criteria->tenantId,
            'account' => $criteria->accountCode,
        ];

        if (null !== $criteria->from) {
            $sql .= ' AND created_at >= :fromDate';
            $params['fromDate'] = $criteria->from;
        }

        if (null !== $criteria->to) {
            $sql .= ' AND created_at <= :toDate';
            $params['toDate'] = $criteria->to;
        }

        if (null !== $criteria->vendorId) {
            $sql .= ' AND vendor_id = :vendorId';
            $params['vendorId'] = $criteria->vendorId;
        }

        if (null !== $criteria->currency) {
            $sql .= ' AND currency = :currency';
            $params['currency'] = $criteria->currency;
        }

        return self::floatValue($this->connection->fetchOne($sql, $params));
    }

    /**
     * @return list<object{currency:string,balanceCents:int}>
     * @throws Exception
     */
    public function balancesForVendor(string $vendorId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT currency, CAST(ROUND(SUM(amount) * 100) AS SIGNED) AS balance_cents FROM vendor_ledger_entries WHERE vendor_id = :vendorId GROUP BY currency',
            ['vendorId' => $vendorId],
        );

        return array_map(
            static fn (array $row): object => (object) [
                'currency' => self::stringValue($row['currency'] ?? null),
                'balanceCents' => self::intValue($row['balance_cents'] ?? null),
            ],
            $rows,
        );
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function nullableStringValue(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }

    private static function floatValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
