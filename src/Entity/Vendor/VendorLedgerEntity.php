<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorLedgerRepository::class)]
#[ORM\Table(name: 'vendor_ledger_entries')]
class VendorLedgerEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 64)] public string $tenantId;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] public ?string $vendorId = null;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceType;
    #[ORM\Column(type: 'string', length: 64)] public string $referenceId;
    #[ORM\Column(type: 'string', length: 64)] public string $debitAccount;
    #[ORM\Column(type: 'string', length: 64)] public string $creditAccount;
    #[ORM\Column(type: 'float')] public float $amount;
    #[ORM\Column(type: 'string', length: 8)] public string $currency;
    public string $createdAt;

    public function __construct(
        string $tenantId,
        mixed $vendorId,
        string $referenceType,
        string $referenceId,
        mixed $debitAccount,
        ?string $creditAccount = null,
        mixed $amount = null,
        ?string $currency = null,
        mixed $legacyVendorId = null,
        mixed $legacyOccurredAt = null,
        ?string $occurredAt = null,
    ) {
        parent::__construct();

        if (is_numeric($debitAccount) && is_string($amount) && null !== $currency) {
            $this->createdAt = is_scalar($legacyOccurredAt) ? (string) $legacyOccurredAt : date('Y-m-d H:i:s');
            $this->tenantId = is_scalar($vendorId) ? (string) $vendorId : '';
            $this->vendorId = is_scalar($legacyVendorId) ? (string) $legacyVendorId : null;
            $this->referenceType = $amount;
            $this->referenceId = $currency;
            $this->debitAccount = $referenceType;
            $this->creditAccount = $referenceId;
            $this->amount = (float) $debitAccount;
            $this->currency = (string) $creditAccount;

            return;
        }

        $this->createdAt = $occurredAt ?? date('Y-m-d H:i:s');
        if (null !== $occurredAt) {
            $this->initializeObjectAudit(new \DateTimeImmutable($occurredAt));
        }

        $this->tenantId = $tenantId;
        $this->vendorId = is_scalar($vendorId) ? (string) $vendorId : null;
        $this->referenceType = $referenceType;
        $this->referenceId = $referenceId;
        $this->debitAccount = is_scalar($debitAccount) ? (string) $debitAccount : '';
        $this->creditAccount = (string) $creditAccount;
        $this->amount = is_numeric($amount) ? (float) $amount : 0.0;
        $this->currency = (string) $currency;
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
