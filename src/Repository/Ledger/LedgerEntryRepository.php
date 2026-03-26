<?php

declare(strict_types=1);

namespace App\Repository\Ledger;

use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use Doctrine\DBAL\Connection;

final class LedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function insert(LedgerEntry $entry): void
    {
        $this->db->insert('ledger_entries', [
            'id' => $entry->id,
            'tenant_id' => $entry->tenantId,
            'debit_account' => $entry->debitAccount,
            'credit_account' => $entry->creditAccount,
            'amount' => $entry->amount,
            'currency' => $entry->currency,
            'reference_type' => $entry->referenceType,
            'reference_id' => $entry->referenceId,
            'vendor_id' => $entry->vendorId,
            'created_at' => $entry->createdAt,
        ]);
    }

    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $sql = 'SELECT * FROM ledger_entries WHERE tenant_id=:t AND reference_type=:rt AND reference_id=:rid';
        $params = [
            't' => $tenantId,
            'rt' => $referenceType,
            'rid' => $referenceId,
        ];

        if (null !== $vendorId) {
            $sql .= ' AND vendor_id=:v';
            $params['v'] = $vendorId;
        }

        $sql .= ' ORDER BY created_at';
        $rows = $this->db->fetchAllAssociative($sql, $params);

        return array_map(
            fn (array $row): LedgerEntry => new LedgerEntry(
                $this->stringCell($row, 'id'),
                $this->stringCell($row, 'tenant_id'),
                $this->stringCell($row, 'debit_account'),
                $this->stringCell($row, 'credit_account'),
                $this->floatCell($row, 'amount'),
                $this->stringCell($row, 'currency'),
                $this->stringCell($row, 'reference_type'),
                $this->stringCell($row, 'reference_id'),
                $this->nullableStringCell($row, 'vendor_id'),
                $this->stringCell($row, 'created_at'),
            ),
            $rows,
        );
    }

    public function sumByAccount(string $tenantId, string $accountCode, ?string $from = null, ?string $to = null, ?string $vendorId = null, ?string $currency = null): float
    {
        $where = ['tenant_id=:t AND (debit_account=:a OR credit_account=:a)'];
        $params = ['t' => $tenantId, 'a' => $accountCode];

        if (null !== $from && '' !== $from) {
            $where[] = 'created_at >= :from';
            $params['from'] = $from.' 00:00:00';
        }

        if (null !== $to && '' !== $to) {
            $where[] = 'created_at <= :to';
            $params['to'] = $to.' 23:59:59';
        }

        if (null !== $vendorId && '' !== $vendorId) {
            $where[] = 'vendor_id = :v';
            $params['v'] = $vendorId;
        }

        if (null !== $currency && '' !== $currency) {
            $where[] = 'currency = :currency';
            $params['currency'] = $currency;
        }

        $sql = 'SELECT SUM(CASE WHEN debit_account=:a THEN amount ELSE 0 END) AS d, SUM(CASE WHEN credit_account=:a THEN amount ELSE 0 END) AS c FROM ledger_entries WHERE '.implode(' AND ', $where);
        $row = $this->db->fetchAssociative($sql, $params);
        $debit = is_array($row) ? $this->nullableFloatCell($row, 'd') ?? 0.0 : 0.0;
        $credit = is_array($row) ? $this->nullableFloatCell($row, 'c') ?? 0.0 : 0.0;

        return $debit - $credit;
    }

    public function balancesForVendor(string $vendorId): array
    {
        $rows = $this->db->fetchAllAssociative(
            "SELECT currency, SUM(CASE WHEN debit_account='VENDOR_PAYABLE' THEN amount ELSE 0 END) - SUM(CASE WHEN credit_account='VENDOR_PAYABLE' THEN amount ELSE 0 END) AS balance FROM ledger_entries WHERE vendor_id = :vendorId GROUP BY currency ORDER BY currency",
            ['vendorId' => $vendorId],
        );

        return array_map(
            fn (array $row): object => (object) [
                'currency' => $this->stringCell($row, 'currency'),
                'balanceCents' => (int) round(($this->nullableFloatCell($row, 'balance') ?? 0.0) * 100),
            ],
            $rows,
        );
    }

    /** @param array<string, mixed> $row */
    private function stringCell(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<string, mixed> $row */
    private function nullableStringCell(array $row, string $key): ?string
    {
        $value = $row[$key] ?? null;

        return is_scalar($value) ? (string) $value : null;
    }

    /** @param array<string, mixed> $row */
    private function floatCell(array $row, string $key): float
    {
        $value = $row[$key] ?? 0.0;

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /** @param array<string, mixed> $row */
    private function nullableFloatCell(array $row, string $key): ?float
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }
}
