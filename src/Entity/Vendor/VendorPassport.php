<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_passport')]
class VendorPassport
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'passport', targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Vendor $vendor;

    #[ORM\Column(length: 64)]
    private string $taxId;

    #[ORM\Column(length: 64)]
    private string $registrationCountry;

    #[ORM\Column(length: 32)]
    private string $kycStatus = 'unverified';

    public function __construct(Vendor $vendor, string $taxId, string $country)
    {
        $this->vendor = $vendor;
        $this->taxId = $taxId;
        $this->registrationCountry = $country;
    }
}
