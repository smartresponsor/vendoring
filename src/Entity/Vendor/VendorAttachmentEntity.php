<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorAttachmentRepository::class)]
#[ORM\Table(name: 'vendor_attachment')]
class VendorAttachmentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'attachments')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255)] private string $title;
    #[ORM\Column(type: 'string', length: 1024)] private string $filePath;
    #[ORM\Column(type: 'string', length: 64, nullable: true)] private ?string $category = null;
    public function __construct(VendorEntity $vendor, string $title, string $filePath, ?string $category = null)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->title = $title;
        $this->filePath = $filePath;
        $this->category = $category;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
