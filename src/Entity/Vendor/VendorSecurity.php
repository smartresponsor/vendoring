<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorSecurityRepository')]
#[ORM\Table(name: 'vendor_security')]
class VendorSecurity
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $apiKey = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $webhookSecret = null;

    #[ORM\Column(type: 'boolean')]
    private bool $twoFactorEnabled = false;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
