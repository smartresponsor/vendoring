<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_document_attachment')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_document_attachment_document', columns: ['vendor_document_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorDocumentAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorDocument::class)]
    #[ORM\JoinColumn(name: 'vendor_document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorDocument $document;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorDocument $document, string $filePath)
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
