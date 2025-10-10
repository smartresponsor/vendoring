<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorBillingRepository')]
#[ORM\Table(name: 'vendor_billing')]
class VendorBilling
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $swift = null;

    #[ORM\Column(length: 32)]
    private string $payoutMethod = 'bank'; // bank|stripe|paypal

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $billingEmail = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastPayoutAt = null;

    #[ORM\Column(length: 24)]
    private string $payoutStatus = 'idle'; // idle|requested|processing|completed|failed

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function markPayoutRequested(): void
    {
        $this->payoutStatus = 'requested';
    }

    public function markPayoutCompleted(): void
    {
        $this->payoutStatus = 'completed';
        $this->lastPayoutAt = new \DateTimeImmutable();
    }
}
