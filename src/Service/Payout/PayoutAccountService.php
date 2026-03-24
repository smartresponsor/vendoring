<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Payout;

use App\Entity\Vendor\Payout\PayoutAccount;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\ServiceInterface\Payout\PayoutAccountServiceInterface;
use Symfony\Component\Uid\Uuid;

final class PayoutAccountService implements PayoutAccountServiceInterface
{
    public function __construct(private readonly PayoutAccountRepositoryInterface $accounts)
    {
    }

    public function upsertFromPayload(array $payload): PayoutAccount
    {
        foreach (['tenantId', 'vendorId', 'provider', 'accountRef', 'currency'] as $field) {
            if (!isset($payload[$field])) {
                throw new \InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        $account = new PayoutAccount(
            Uuid::v4()->toRfc4122(),
            (string) $payload['tenantId'],
            (string) $payload['vendorId'],
            (string) $payload['provider'],
            (string) $payload['accountRef'],
            (string) $payload['currency'],
            (bool) ($payload['active'] ?? true),
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        );

        $this->accounts->upsert($account);

        return $account;
    }
}
