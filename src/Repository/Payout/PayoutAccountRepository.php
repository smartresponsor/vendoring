<?php

declare(strict_types=1);

namespace App\Repository\Payout;

use App\Entity\Payout\PayoutAccount;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class PayoutAccountRepository implements PayoutAccountRepositoryInterface
{
    public function __construct(private Connection $db)
    {
    }

    /** @throws Exception */
    public function get(string $tenantId, string $vendorId): ?PayoutAccount
    {
        $accountRow = $this->db->fetchAssociative(
            'SELECT * FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v',
            ['t' => $tenantId, 'v' => $vendorId],
        );
        if (!$accountRow) {
            return null;
        }

        return new PayoutAccount(
            $this->stringCell($accountRow, 'id'),
            $this->stringCell($accountRow, 'tenant_id'),
            $this->stringCell($accountRow, 'vendor_id'),
            $this->stringCell($accountRow, 'provider'),
            $this->stringCell($accountRow, 'account_ref'),
            $this->stringCell($accountRow, 'currency'),
            $this->boolCell($accountRow, 'active'),
            $this->stringCell($accountRow, 'created_at'),
        );
    }

    /** @throws Exception */
    public function upsert(PayoutAccount $account): void
    {
        $exists = $this->db->fetchOne(
            'SELECT COUNT(*) FROM payout_accounts WHERE tenant_id=:t AND vendor_id=:v',
            ['t' => $account->tenantId, 'v' => $account->vendorId],
        );
        if ($this->intValue($exists) > 0) {
            $this->db->update(
                'payout_accounts',
                [
                    'provider' => $account->provider,
                    'account_ref' => $account->accountRef,
                    'currency' => $account->currency,
                    'active' => $account->active ? 1 : 0,
                ],
                ['tenant_id' => $account->tenantId, 'vendor_id' => $account->vendorId],
            );

            return;
        }

        $this->db->insert('payout_accounts', [
            'id' => $account->id,
            'tenant_id' => $account->tenantId,
            'vendor_id' => $account->vendorId,
            'provider' => $account->provider,
            'account_ref' => $account->accountRef,
            'currency' => $account->currency,
            'active' => $account->active ? 1 : 0,
            'created_at' => $account->createdAt,
        ]);
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
