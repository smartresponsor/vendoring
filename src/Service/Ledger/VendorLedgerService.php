<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\LedgerEntryDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;
use App\Vendoring\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class VendorLedgerService implements VendorLedgerServiceInterface
{
    public function __construct(private LedgerEntryRepositoryInterface $repo) {}

    /**
     * @throws Exception
     */
    public function record(LedgerEntryDTO $dto): LedgerEntry
    {
        $createdAt = $dto->occurredAt;
        if (null === $createdAt) {
            $occurredAt = new DateTimeImmutable();
            $createdAt = $occurredAt->format('Y-m-d H:i:s');
        }
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
