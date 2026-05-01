<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorCatalogReviewAssignmentEntity;

final class VendorCatalogReviewAssignmentRepository
{
    /** @var list<VendorCatalogReviewAssignmentEntity> */
    private array $items = [];

    public function save(VendorCatalogReviewAssignmentEntity $assignment): void
    {
        $this->items[] = $assignment;
    }

    /** @return list<VendorCatalogReviewAssignmentEntity> */
    public function all(): array
    {
        return $this->items;
    }
}
