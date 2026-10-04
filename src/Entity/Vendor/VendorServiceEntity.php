<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorServiceRepository::class)]
#[ORM\Table(
    name: 'vendor_service',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_vendor_service_vendor_category', columns: ['vendor_id', 'category_id']),
    ],
    indexes: [
        new ORM\Index(name: 'idx_vendor_service_category', columns: ['category_id']),
    ],
)]
class VendorServiceEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'category_id', type: 'string', length: 255)]
    private string $categoryId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $code = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload = [];

    /** @param array<string, mixed> $payload */
    public function __construct(VendorEntity $vendor, string $categoryId, ?string $code = null, array $payload = [])
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->categoryId = trim($categoryId);
        $this->code = $code;
        $this->payload = $payload;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    /** @return array<string, mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @param array<string, mixed> $payload */
    public function update(?string $code, array $payload): self
    {
        $this->code = $code;
        $this->payload = $payload;
        $this->touchModified();

        return $this;
    }
}
