<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository\Payout;

use App\RepositoryInterface\Vendor\Repository\Payout\PayoutAccountRepositoryInterface;
use Doctrine\DBAL\Connection;
use App\Entity\Vendor\Payout\PayoutAccount;
use App\RepositoryInterface\Vendor\Payout\PayoutAccountRepositoryInterface;

final class PayoutAccountRepository implements PayoutAccountRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function get(string $tenantId, string $vendorId): ?PayoutAccount
    {
        $r = $this->db->fetchAssociative("SELECT * FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v", ['t' => $tenantId, 'v' => $vendorId]);
        if (!$r) return null;
        return new PayoutAccount((string)$r['id'], (string)$r['tenant_id'], (string)$r['vendor_id'], (string)$r['provider'], (string)$r['account_ref'], (string)$r['currency'], (bool)$r['active'], (string)$r['created_at']);
    }

    public function upsert(PayoutAccount $a): void
    {
        $exists = $this->db->fetchOne("SELECT COUNT(*) FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v", ['t' => $a->tenantId, 'v' => $a->vendorId]);
        if ((int)$exists > 0) {
            $this->db->update('payout_accounts', ['provider' => $a->provider, 'account_ref' => $a->accountRef, 'currency' => $a->currency, 'active' => $a->active ? 1 : 0], ['tenant_id' => $a->tenantId, 'vendor_id' => $a->vendorId]);
        } else {
            $this->db->insert('payout_accounts', ['id' => $a->id, 'tenant_id' => $a->tenantId, 'vendor_id' => $a->vendorId, 'provider' => $a->provider, 'account_ref' => $a->accountRef, 'currency' => $a->currency, 'active' => $a->active ? 1 : 0, 'created_at' => $a->createdAt]);
        }
    }
}
