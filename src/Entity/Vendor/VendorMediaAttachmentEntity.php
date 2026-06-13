<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorMediaAttachmentRepository::class)]
#[ORM\Table(name: 'vendor_media_attachment')]
class VendorMediaAttachmentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorMediaEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorMediaEntity $media;
    #[ORM\Column(type: 'string', length: 32)] private string $kind;
    #[ORM\Column(type: 'string', length: 1024)] private string $filePath;
    #[ORM\Column(type: 'integer', nullable: true)] private ?int $position = null;
    public function __construct(VendorMediaEntity $media, string $kind, string $filePath, ?int $position = null)
    {
        parent::__construct();
        $this->media = $media;
        $this->kind = $kind;
        $this->filePath = $filePath;
        $this->position = $position;
    }
}
