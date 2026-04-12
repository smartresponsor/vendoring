<?php

declare(strict_types=1);

namespace App\Entity\Ledger;

final class LedgerEntry
{
    public string $type;
    public string $entityId;

    public function __construct(
        public string $id,
        public string $tenantId,
        public string $debitAccount,
        public string $creditAccount,
        public float $amount,
        public string $currency,
        public string $referenceType,
        public string $referenceId,
        public ?string $vendorId,
        public string $createdAt,
    ) {
        $this->type = $this->referenceType;
        $this->entityId = $this->referenceId;
    }
}
