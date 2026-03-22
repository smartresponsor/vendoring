<?php

declare(strict_types=1);

namespace App\Repository\Ledger;

use App\Entity\Vendor\Ledger\LedgerEntry;
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
            static fn (array $row): LedgerEntry => new LedgerEntry(
                (string) $row['id'],
                (string) $row['tenant_id'],
                (string) $row['debit_account'],
                (string) $row['credit_account'],
                (float) $row['amount'],
                (string) $row['currency'],
                (string) $row['reference_type'],
                (string) $row['reference_id'],
                isset($row['vendor_id']) && null !== $row['vendor_id'] ? (string) $row['vendor_id'] : null,
                (string) $row['created_at'],
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
        $debit = (float) ($row['d'] ?? 0);
        $credit = (float) ($row['c'] ?? 0);

        return $debit - $credit;
    }

    public function balancesForVendor(string $vendorId): array
    {
        $rows = $this->db->fetchAllAssociative(
            "SELECT currency, SUM(CASE WHEN debit_account='VENDOR_PAYABLE' THEN amount ELSE 0 END) - SUM(CASE WHEN credit_account='VENDOR_PAYABLE' THEN amount ELSE 0 END) AS balance FROM ledger_entries WHERE vendor_id = :vendorId GROUP BY currency ORDER BY currency",
            ['vendorId' => $vendorId],
        );

        return array_map(
            static fn (array $row): object => (object) [
                'currency' => (string) ($row['currency'] ?? ''),
                'balanceCents' => (int) round(((float) ($row['balance'] ?? 0.0)) * 100),
            ],
            $rows,
        );
    }
}
