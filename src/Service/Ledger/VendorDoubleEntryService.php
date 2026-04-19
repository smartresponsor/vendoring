<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\DoubleEntryDTO;
use App\Vendoring\Entity\Ledger\LedgerEntry;
use App\Vendoring\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorDoubleEntryServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final readonly class VendorDoubleEntryService implements VendorDoubleEntryServiceInterface
{
    public function __construct(private LedgerEntryRepositoryInterface $repo) {}

    /**
     * @param DoubleEntryDTO $dto
     * @return array{0: LedgerEntry}
     * @throws Exception
     */
    public function post(DoubleEntryDTO $dto): array
    {
        $timestamp = $dto->occurredAt;
        if (null === $timestamp) {
            $occurredAt = new DateTimeImmutable();
            $timestamp = $occurredAt->format('Y-m-d H:i:s');
        }

        $entry = new LedgerEntry(
            Uuid::v4()->toRfc4122(),
            $dto->tenantId,
            $dto->debitAccount,
            $dto->creditAccount,
            $dto->amount,
            $dto->currency,
            $dto->referenceType,
            $dto->referenceId,
            $dto->vendorId,
            $timestamp,
        );

        $this->repo->insert($entry);

        return [$entry];
    }
}
