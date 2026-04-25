<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Ledger;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_ledger_entries')]
#[ORM\Index(name: 'idx_vendor_ledger_ref', columns: ['tenant_id', 'reference_type', 'reference_id'])]
#[ORM\Index(name: 'idx_vendor_ledger_vendor', columns: ['vendor_id'])]
final class LedgerEntry
{
    public string $type;
    public string $entityId;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 36)]
        public string $id,
        #[ORM\Column(name: 'tenant_id', type: 'string', length: 255)]
        public string $tenantId,
        #[ORM\Column(name: 'debit_account', type: 'string', length: 64)]
        public string $debitAccount,
        #[ORM\Column(name: 'credit_account', type: 'string', length: 64)]
        public string $creditAccount,
        #[ORM\Column(type: 'float')]
        public float $amount,
        #[ORM\Column(type: 'string', length: 8)]
        public string $currency,
        #[ORM\Column(name: 'reference_type', type: 'string', length: 64)]
        public string $referenceType,
        #[ORM\Column(name: 'reference_id', type: 'string', length: 64)]
        public string $referenceId,
        #[ORM\Column(name: 'vendor_id', type: 'string', length: 255, nullable: true)]
        public ?string $vendorId,
        #[ORM\Column(name: 'created_at', type: 'string', length: 19)]
        public string $createdAt,
    ) {
        $this->type = $this->referenceType;
        $this->entityId = $this->referenceId;
    }
}
