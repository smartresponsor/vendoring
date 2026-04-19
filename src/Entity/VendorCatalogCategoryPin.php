<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorCatalogCategoryPin
{
    private string $id;

    public function __construct(
        private readonly string $categoryId,
        private readonly string $recordId,
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
