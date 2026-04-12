<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Ledger;

use App\DTO\Ledger\DoubleEntryDTO;
use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorDoubleEntryServiceInterface;
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
        $timestamp = $dto->occurredAt ?? (new DateTimeImmutable())->format('Y-m-d H:i:s');

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
