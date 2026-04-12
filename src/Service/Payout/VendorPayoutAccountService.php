<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Payout;

use App\Entity\Payout\PayoutAccount;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutAccountServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class VendorPayoutAccountService implements VendorPayoutAccountServiceInterface
{
    public function __construct(private PayoutAccountRepositoryInterface $accounts) {}

    /**
     * @param array<string, mixed> $payload
     * @return PayoutAccount
     * @throws Exception
     */
    public function upsertFromPayload(array $payload): PayoutAccount
    {
        foreach (['tenantId', 'vendorId', 'provider', 'accountRef', 'currency'] as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException(sprintf('%s required', $field));
            }
        }

        $createdAt = new DateTimeImmutable();

        $account = new PayoutAccount(
            Uuid::v4()->toRfc4122(),
            $this->requiredString($payload, 'tenantId'),
            $this->requiredString($payload, 'vendorId'),
            $this->requiredString($payload, 'provider'),
            $this->requiredString($payload, 'accountRef'),
            strtoupper($this->requiredString($payload, 'currency')),
            $this->boolValue($payload['active'] ?? true),
            $createdAt->format('Y-m-d H:i:s'),
        );

        $this->accounts->upsert($account);

        return $account;
    }

    /** @param array<string, mixed> $payload */
    private function requiredString(array $payload, string $field): string
    {
        $value = $payload[$field] ?? null;
        $normalized = null;

        if (is_string($value)) {
            $normalized = trim($value);
        }

        if (is_int($value) || is_float($value)) {
            $normalized = (string) $value;
        }

        if (null !== $normalized && '' !== $normalized) {
            return $normalized;
        }

        throw new InvalidArgumentException(sprintf('%s required', $field));
    }

    private function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return 0 !== $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }
}
