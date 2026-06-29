<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Objecting\EntityTrait\Embeddable\ObjectCodeEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Canonical pin record for a catalog category.
 *
 * object_code → categoryId (the category this pin belongs to)
 * recordId    → the pinned record identifier within the category
 * position    → sort order within the category
 *
 * Schema is stable: no JSON payload, no code/payload split.
 * schema:update --force is safe on this entity.
 */
#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_pin')]
class VendorCatalogCategoryPinEntity extends VendorAbstractEntity
{
    use ObjectCodeEmbeddableTrait;

    #[ORM\Column(type: 'string', length: 255, name: 'record_id')]
    private string $recordId;

    #[ORM\Column(type: 'integer', name: 'position')]
    private int $position;

    public function __construct(string $categoryId, string $recordId, int $position = 0)
    {
        parent::__construct('active');
        $this->initializeObjectCode($categoryId);
        $this->recordId = $recordId;
        $this->position = $position;
    }

    public function categoryId(): string
    {
        return $this->getObjectCode() ?? '';
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
        $this->position = $position;
    }
}
