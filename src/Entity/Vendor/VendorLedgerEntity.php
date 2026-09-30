<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorLedgerRepository::class)]
#[ORM\Table(name: 'vendor_ledger_entries')]
class VendorLedgerEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $vendorId;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceType;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceId;
    #[ORM\Column(type: 'string', length: 64)] public string $debitAccount;
    #[ORM\Column(type: 'string', length: 64)] public string $creditAccount;
    #[ORM\Column(type: 'float')] public float $amount;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    public string $createdAt;

    public function __construct(
        string $vendorId,
        string $referenceType,
        string $referenceId,
        string $debitAccount,
        string $creditAccount,
        float $amount,
        string $currency,
        ?string $occurredAt = null,
    ) {
        parent::__construct();

        $this->createdAt = $occurredAt ?? date('Y-m-d H:i:s');
        if (null !== $occurredAt) {
            $this->initializeObjectAudit(new \DateTimeImmutable($occurredAt));
        }

        $this->vendorId = $vendorId;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
        $this->debitAccount = $debitAccount;
        $this->creditAccount = $creditAccount;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function __get(string $name): mixed
    {
        if ('id' === $name) {
            return $this->getObjectUuid();
        }

        if ('type' === $name) {
            return $this->referenceType;
        }

        if ('entityId' === $name) {
            return $this->referenceId;
        }

        return null;
    }

    public function getType(): string
    {
        return $this->referenceType;
    }

    public function getEntityId(): string
    {
        return $this->referenceId;
    }
}
