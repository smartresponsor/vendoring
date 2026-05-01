<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Ledger;

use App\Vendoring\DTO\Ledger\VendorDoubleEntryDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerEntryEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerEntryRepositoryInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorDoubleEntryServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;

final readonly class VendorDoubleEntryService implements VendorDoubleEntryServiceInterface
{
    public function __construct(private VendorLedgerEntryRepositoryInterface $repo) {}

    /**
     * @param VendorDoubleEntryDTO $dto
     * @return array{0: VendorLedgerEntryEntity}
     * @throws Exception
     */
    public function post(VendorDoubleEntryDTO $dto): array
    {
        $timestamp = $dto->occurredAt;
        if (null === $timestamp) {
            $occurredAt = new DateTimeImmutable();
            $timestamp = $occurredAt->format('Y-m-d H:i:s');
        }

        $entry = new VendorLedgerEntryEntity(
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
