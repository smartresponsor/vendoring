<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_billing')]
class VendorBilling
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'billing', targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Vendor $vendor;

    #[ORM\Column(length: 34, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $swift = null;

    #[ORM\Column(length: 16)]
    private string $payoutMethod = 'bank';

    public function __construct(Vendor $vendor) { $this->vendor = $vendor; }
}
