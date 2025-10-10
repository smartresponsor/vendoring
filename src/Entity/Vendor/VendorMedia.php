<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorMediaRepository')]
#[ORM\Table(name: 'vendor_media')]
class VendorMedia
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bannerPath = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $gallery = null; // array of file paths/URLs

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
