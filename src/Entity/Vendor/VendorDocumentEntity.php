<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorDocumentRepository::class)]
#[ORM\Table(name: 'vendor_document')]
class VendorDocumentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64)] private string $type;
    #[ORM\Column(type: 'string', length: 1024)] private string $filePath;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $expiresAt = null;
    #[ORM\Column(type: 'integer', nullable: true)] private ?int $uploaderId = null;
    public function __construct(VendorEntity $vendor, string $type, string $filePath, ?\DateTimeImmutable $expiresAt = null, ?int $uploaderId = null)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->type = $type;
        $this->filePath = $filePath;
        $this->expiresAt = $expiresAt;
        $this->uploaderId = $uploaderId;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUploaderId(): ?int
    {
        return $this->uploaderId;
    }
}
