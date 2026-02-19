<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Vendor\Ledger;

use App\DTO\Vendor\Ledger\DoubleEntryDTO;
use App\Entity\Vendor\Ledger\LedgerEntry;
use App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Vendor\Ledger\DoubleEntryServiceInterface;
use Symfony\Component\Uid\Uuid;

final class DoubleEntryService implements DoubleEntryServiceInterface
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
