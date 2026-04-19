<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorCatalogCategoryChangeRequest;

final class VendorCatalogCategoryChangeRequestRepository
{
    /** @var array<string, VendorCatalogCategoryChangeRequest> */
    private array $items = [];

    public function save(VendorCatalogCategoryChangeRequest $request): void
    {
        $this->items[$request->id()] = $request;
    }

    public function byId(string $id): ?VendorCatalogCategoryChangeRequest
    {
        return $this->items[$id] ?? null;
    }
}
