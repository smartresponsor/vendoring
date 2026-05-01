<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorCatalogCategoryChangeRequestEntity;

final class VendorCatalogCategoryChangeRequestRepository
{
    /** @var array<string, VendorCatalogCategoryChangeRequestEntity> */
    private array $items = [];

    public function save(VendorCatalogCategoryChangeRequestEntity $request): void
    {
        $this->items[$request->id()] = $request;
    }

    public function byId(string $id): ?VendorCatalogCategoryChangeRequestEntity
    {
        return $this->items[$id] ?? null;
    }
}
