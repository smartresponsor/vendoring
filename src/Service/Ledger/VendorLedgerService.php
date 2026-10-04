<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\VendorLedgerDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntity;
use App\Vendoring\RepositoryInterface\VendorLedgerRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorLedgerServiceInterface;
use Doctrine\DBAL\Exception;

final readonly class VendorLedgerService implements VendorLedgerServiceInterface
{
    public function __construct(private VendorLedgerRepositoryInterface $repo)
    {
    }

    /**
     * @throws Exception
     */
    public function record(VendorLedgerDTO $dto): VendorLedgerEntity
    {
        $amount = $dto->amountCents / 100;

        [$debitAccount, $creditAccount] = match ($dto->direction) {
            'debit' => [$dto->type, 'VENDOR_PAYABLE'],
            'credit' => ['VENDOR_PAYABLE', $dto->type],
            default => throw new \InvalidArgumentException(sprintf('Unsupported ledger direction "%s".', $dto->direction)),
        };

        $entry = new VendorLedgerEntity(
            vendorId: $dto->vendorId,
            referenceType: $dto->type,
            referenceId: $dto->entityId,
            debitAccount: $debitAccount,
            creditAccount: $creditAccount,
            amount: $amount,
            currency: $dto->currency,
            occurredAt: $dto->occurredAt,
        );

        $this->repo->insert($entry);

        return $entry;
    }
}
