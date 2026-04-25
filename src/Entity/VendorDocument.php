<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\VendorDocumentRepository')]
#[ORM\Table(name: 'vendor_document')]
#[ORM\Index(name: 'idx_vendor_document_vendor_created', columns: ['vendor_id', 'created_at'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'string', length: 64)]
    private string $type;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(name: 'uploader_id', type: 'integer', nullable: true)]
    private ?int $uploaderId = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(Vendor $vendor, string $type, string $filePath)
    {
        $this->vendor = $vendor;
        $this->type = $type;
        $this->filePath = $filePath;
        $this->createdAt = new DateTimeImmutable();
    }

    public function assignMetadata(?DateTimeImmutable $expiresAt = null, ?int $uploaderId = null): void
    {
        $this->expiresAt = $expiresAt;
        $this->uploaderId = $uploaderId;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
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

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUploaderId(): ?int
    {
        return $this->uploaderId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
