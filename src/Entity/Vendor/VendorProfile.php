<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorProfileRepository')]
#[ORM\Table(name: 'vendor_profile')]
class VendorProfile
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $about = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $socials = null; // {fb:'', ig:'', li:'', x:''}

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $seoDescription = null;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
