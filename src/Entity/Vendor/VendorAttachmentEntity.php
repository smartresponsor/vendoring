<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorAttachmentRepository')]
#[ORM\Table(name: 'vendor_attachment')]
#[ORM\Index(name: 'idx_vendor_attachment_vendor_created', columns: ['vendor_id', 'created_at'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAttachmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $category;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorEntity $vendor, string $title, string $filePath, ?string $category = null)
    {
        $this->vendor = $vendor;
        $this->title = $title;
        $this->filePath = $filePath;
        $this->category = $category;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
