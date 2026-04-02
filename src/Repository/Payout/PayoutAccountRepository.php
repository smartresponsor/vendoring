<?php

declare(strict_types=1);

namespace App\Repository\Payout;

use App\Entity\Payout\PayoutAccount;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use Doctrine\DBAL\Connection;

final class PayoutAccountRepository implements PayoutAccountRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function get(string $tenantId, string $vendorId): ?PayoutAccount
    {
        $r = $this->db->fetchAssociative('SELECT * FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v', ['t' => $tenantId, 'v' => $vendorId]);
        if (!$r) {
            return null;
        }

        return new PayoutAccount($this->stringCell($r, 'id'), $this->stringCell($r, 'tenant_id'), $this->stringCell($r, 'vendor_id'), $this->stringCell($r, 'provider'), $this->stringCell($r, 'account_ref'), $this->stringCell($r, 'currency'), $this->boolCell($r, 'active'), $this->stringCell($r, 'created_at'));
    }

    public function upsert(PayoutAccount $a): void
    {
        $exists = $this->db->fetchOne('SELECT COUNT(*) FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v', ['t' => $a->tenantId, 'v' => $a->vendorId]);
        if ($this->intValue($exists) > 0) {
            $this->db->update('payout_accounts', ['provider' => $a->provider, 'account_ref' => $a->accountRef, 'currency' => $a->currency, 'active' => $a->active ? 1 : 0], ['tenant_id' => $a->tenantId, 'vendor_id' => $a->vendorId]);
        } else {
            $this->db->insert('payout_accounts', ['id' => $a->id, 'tenant_id' => $a->tenantId, 'vendor_id' => $a->vendorId, 'provider' => $a->provider, 'account_ref' => $a->accountRef, 'currency' => $a->currency, 'active' => $a->active ? 1 : 0, 'created_at' => $a->createdAt]);
        }
    }

    /** @param array<string, mixed> $row */
    private function stringCell(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<string, mixed> $row */
    private function boolCell(array $row, string $key): bool
    {
        return filter_var($row[$key] ?? false, FILTER_VALIDATE_BOOL);
    }

    private function intValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
