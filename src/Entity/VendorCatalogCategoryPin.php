<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_pin')]
final class VendorCatalogCategoryPin
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $id;

    public function __construct(
        #[ORM\Column(name: 'category_id', type: 'string', length: 255)]
        private readonly string $categoryId,
        #[ORM\Column(name: 'record_id', type: 'string', length: 255)]
        private readonly string $recordId,
        #[ORM\Column(type: 'integer')]
        private int $position,
    ) {
        $this->id = sprintf('%s:%s', $categoryId, $recordId);
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
        $this->position = $position;
    }
}
