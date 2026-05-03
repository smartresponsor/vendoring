<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_pin')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_catalog_category_pin_record', columns: ['category_id', 'record_id'])]
#[ORM\Index(name: 'idx_vendor_catalog_category_pin_category', columns: ['category_id', 'position'])]
final class VendorCatalogCategoryPinEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 96)]
    private string $categoryId;

    #[ORM\Column(name: 'record_id', type: 'string', length: 96)]
    private string $recordId;

    #[ORM\Column(type: 'integer')]
    private int $position;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $categoryId, string $recordId, int $position)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->categoryId = trim($categoryId);
        $this->recordId = trim($recordId);
        $this->position = max(0, $position);
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function recordId(): string
    {
        return $this->recordId;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function reorder(int $position): void
    {
        $this->position = max(0, $position);
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
