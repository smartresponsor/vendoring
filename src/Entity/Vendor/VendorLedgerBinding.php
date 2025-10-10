<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorLedgerBindingRepository')]
#[ORM\Table(name: 'vendor_ledger_binding')]
class VendorLedgerBinding
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 96, unique: true)]
    private string $ledgerAccountId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Vendor $vendor, string $ledgerAccountId)
    {
        $this->vendor = $vendor;
        $this->ledgerAccountId = $ledgerAccountId;
        $this->createdAt = new \DateTimeImmutable();
    }
}
