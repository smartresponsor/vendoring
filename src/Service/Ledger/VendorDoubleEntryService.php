<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\VendorDoubleEntryDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorDoubleEntryServiceInterface;
use Doctrine\DBAL\Exception;

final readonly class VendorDoubleEntryService implements VendorDoubleEntryServiceInterface
{
    public function __construct(private VendorLedgerRepositoryInterface $repo)
    {
    }

    /**
     * @return array{0: VendorLedgerEntity}
     *
     * @throws Exception
     */
    public function post(VendorDoubleEntryDTO $dto): array
    {
        $entry = new VendorLedgerEntity(
            tenantId: $dto->tenantId,
            vendorId: $dto->vendorId,
            referenceType: $dto->referenceType,
            referenceId: $dto->referenceId,
            debitAccount: $dto->debitAccount,
            creditAccount: $dto->creditAccount,
            amount: $dto->amount,
            currency: $dto->currency,
            occurredAt: $dto->occurredAt,
        );

        $this->repo->insert($entry);

        return [$entry];
    }
}
