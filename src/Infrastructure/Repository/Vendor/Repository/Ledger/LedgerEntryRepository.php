<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository\Ledger;

use App\RepositoryInterface\Vendor\Repository\Ledger\LedgerEntryRepositoryInterface;
use Doctrine\DBAL\Connection;
use App\Entity\Vendor\Ledger\LedgerEntry;
use App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface;

final class LedgerEntryRepository implements LedgerEntryRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function insert(LedgerEntry $e): void
    {
        $this->db->insert('ledger_entries', [
            'id' => $e->id, 'tenant_id' => $e->tenantId, 'debit_account' => $e->debitAccount, 'credit_account' => $e->creditAccount,
            'amount' => $e->amount, 'currency' => $e->currency, 'reference_type' => $e->referenceType, 'reference_id' => $e->referenceId,
            'vendor_id' => $e->vendorId, 'created_at' => $e->createdAt,
        ]);
    }

    public function listByRef(string $tenantId, string $referenceType, string $referenceId, ?string $vendorId = null): array
    {
        $sql = "SELECT * FROM ledger_entries WHERE tenant_id=:t AND reference_type=:rt AND reference_id=:rid";
        $params = ['t' => $tenantId, 'rt' => $referenceType, 'rid' => $referenceId];
        if ($vendorId !== null) {
            $sql .= " AND vendor_id=:v";
            $params['v'] = $vendorId;
        }
        $sql .= " ORDER BY created_at";
        $rows = $this->db->fetchAllAssociative($sql, $params);
        return array_map(fn($r) => new LedgerEntry((string)$r['id'], (string)$r['tenant_id'], (string)$r['debit_account'], (string)$r['credit_account'], (float)$r['amount'], (string)$r['currency'], (string)$r['reference_type'], (string)$r['reference_id'], $r['vendor_id'] ? (string)$r['vendor_id'] : null, (string)$r['created_at']), $rows);
    }

    public function sumByAccount(string $tenantId, string $accountCode, ?string $from = null, ?string $to = null, ?string $vendorId = null): float
    {
        $where = ["tenant_id=:t AND (debit_account=:a OR credit_account=:a)"];
        $params = ['t' => $tenantId, 'a' => $accountCode];
        if ($from) {
            $where[] = "created_at >= :from";
            $params['from'] = $from . ' 00:00:00';
        }
        if ($to) {
            $where[] = "created_at <= :to";
            $params['to'] = $to . ' 23:59:59';
        }
        if ($vendorId) {
            $where[] = "vendor_id = :v";
            $params['v'] = $vendorId;
        }
        $sql = "SELECT SUM(CASE WHEN debit_account=:a THEN amount ELSE 0 END) AS d, SUM(CASE WHEN credit_account=:a THEN amount ELSE 0 END) AS c FROM ledger_entries WHERE " . implode(" AND ", $where);
        $r = $this->db->fetchAssociative($sql, $params);
        $debit = (float)($r['d'] ?? 0);
        $credit = (float)($r['c'] ?? 0);
        return $debit - $credit;
    }
}
