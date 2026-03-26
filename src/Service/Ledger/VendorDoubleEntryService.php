<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Ledger;

use App\DTO\Ledger\DoubleEntryDTO;
use App\Entity\Ledger\LedgerEntry;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Ledger\VendorDoubleEntryServiceInterface;
use Symfony\Component\Uid\Uuid;

final class VendorDoubleEntryService implements VendorDoubleEntryServiceInterface
{
    public function __construct(private readonly LedgerEntryRepositoryInterface $repo)
    {
    }

    /**
     * @return array{0: LedgerEntry}
     */
    public function post(DoubleEntryDTO $dto): array
    {
        $ts = $dto->occurredAt ?? (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $e = new LedgerEntry(
            Uuid::v4()->toRfc4122(),
            $dto->tenantId,
            $dto->debitAccount,
            $dto->creditAccount,
            $dto->amount,
            $dto->currency,
            $dto->referenceType,
            $dto->referenceId,
            $dto->vendorId,
            $ts,
        );

        $this->repo->insert($e);

        return [$e];
    }
}
