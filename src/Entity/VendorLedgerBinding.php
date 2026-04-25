<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\VendorLedgerBindingRepository')]
#[ORM\Table(name: 'vendor_ledger_binding')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_ledger_binding_vendor', columns: ['vendor_id'])]
#[ORM\UniqueConstraint(name: 'uniq_vendor_ledger_binding_external', columns: ['ledger_vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorLedgerBinding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private readonly Vendor $vendor;

    #[ORM\Column(name: 'ledger_vendor_id', type: 'string', length: 64)]
    private readonly string $ledgerVendorId;

    public function __construct(Vendor $vendor, string $ledgerVendorId)
    {
        $this->vendor = $vendor;
        $this->ledgerVendorId = $ledgerVendorId;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getLedgerVendorId(): string
    {
        return $this->ledgerVendorId;
    }
}
