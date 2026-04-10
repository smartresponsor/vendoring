<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Ledger;

use App\DTO\Ledger\LedgerEntryDTO;
use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class VendorLedgerService implements VendorLedgerServiceInterface
{
    public function __construct(private LedgerEntryRepositoryInterface $repo)
    {
    }

    public function record(LedgerEntryDTO $dto): LedgerEntry
    {
        $createdAt = $dto->occurredAt ?? new DateTimeImmutable()->format('Y-m-d H:i:s');
        $amount = $dto->amountCents / 100;

        [$debitAccount, $creditAccount] = match ($dto->direction) {
            'debit' => [$dto->type, 'VENDOR_PAYABLE'],
            'credit' => ['VENDOR_PAYABLE', $dto->type],
            default => throw new InvalidArgumentException(sprintf('Unsupported ledger direction "%s".', $dto->direction)),
        };

        $entry = new LedgerEntry(
            Uuid::v4()->toRfc4122(),
            $dto->tenantId,
            $debitAccount,
            $creditAccount,
            $amount,
            $dto->currency,
            $dto->type,
            $dto->entityId,
            $dto->vendorId,
            $createdAt,
        );

        $this->repo->insert($entry);

        return $entry;
    }
}
