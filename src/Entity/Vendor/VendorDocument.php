<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorDocumentRepository')]
#[ORM\Table(name: 'vendor_document')]
class VendorDocument
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 48)]
    private string $type; // e.g., W9, LICENSE, INSURANCE

    #[ORM\Column(length: 255)]
    private string $filePath;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $uploaderId = null;

    public function __construct(Vendor $vendor, string $type, string $filePath)
    {
        $this->vendor = $vendor;
        $this->type = $type;
        $this->filePath = $filePath;
    }
}
