<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorDocumentAttachmentRepository::class)]
#[ORM\Table(name: 'vendor_document_attachment')]
class VendorDocumentAttachmentEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(targetEntity: VendorDocumentEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorDocumentEntity $document;
    #[ORM\Column(type: 'string', length: 1024)] private string $filePath;
    public function __construct(VendorDocumentEntity $document, string $filePath)
    {
        parent::__construct();
        $this->document = $document;
        $this->filePath = $filePath;
    }

    public function getDocument(): VendorDocumentEntity
    {
        return $this->document;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
