<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorLedgerEntryRepository::class)]
#[ORM\Table(name: 'vendor_ledger_entries')]
class VendorLedgerEntryEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $tenantId;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] public ?string $vendorId = null;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceType;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceId;
    #[ORM\Column(type: 'string', length: 64)] public string $debitAccount;
    #[ORM\Column(type: 'string', length: 64)] public string $creditAccount;
    #[ORM\Column(type: 'float')] public float $amount;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    public function __construct(string $tenantId, ?string $vendorId, string $referenceType, string $referenceId, string $debitAccount, string $creditAccount, float $amount, string $currency)
    {
        parent::__construct();
        $this->tenantId = $tenantId;
        $this->vendorId = $vendorId;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
        $this->debitAccount = $debitAccount;
        $this->creditAccount = $creditAccount;
        $this->amount = $amount;
        $this->currency = $currency;
    }
}
