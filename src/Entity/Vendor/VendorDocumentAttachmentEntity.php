<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_document_attachment')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_document_attachment_document', columns: ['vendor_document_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorDocumentAttachmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorDocumentEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorDocumentEntity $document;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorDocumentEntity $document, string $filePath)
    {
        $this->document = $document;
        $this->filePath = $filePath;
        $this->createdAt = new DateTimeImmutable();
    }

    public function update(string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
