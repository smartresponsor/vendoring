<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_media_attachment')]
#[ORM\Index(name: 'idx_vendor_media_attachment_media_kind', columns: ['vendor_media_id', 'kind'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorMediaAttachment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorMedia::class)]
    #[ORM\JoinColumn(name: 'vendor_media_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorMedia $media;

    #[ORM\Column(type: 'string', length: 32)]
    private string $kind;

    #[ORM\Column(name: 'file_path', type: 'string', length: 1024)]
    private string $filePath;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(VendorMedia $media, string $kind, string $filePath, ?int $position = null)
    {
        $this->media = $media;
        $this->kind = $kind;
        $this->filePath = $filePath;
        $this->position = $position;
        $this->createdAt = new DateTimeImmutable();
    }
}
