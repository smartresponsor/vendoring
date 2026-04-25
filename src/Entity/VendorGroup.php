<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_group')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_group_vendor_code', columns: ['vendor_id', 'code'])]
final class VendorGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'string', length: 64)]
    private string $code;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @param array<string, mixed> $meta */
    public function __construct(Vendor $vendor, string $code, string $name, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->code = $code;
        $this->name = $name;
        $this->meta = $meta;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    /** @param array<string, mixed> $meta */
    public function update(string $name, string $status = 'active', array $meta = []): void
    {
        $this->name = $name;
        $this->status = $status;
        $this->meta = [] === $meta ? $this->meta : $meta;
        $this->updatedAt = new DateTimeImmutable();
    }
}
