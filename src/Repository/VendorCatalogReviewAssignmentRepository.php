<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\VendorCatalogReviewAssignment;

final class VendorCatalogReviewAssignmentRepository
{
    /** @var list<VendorCatalogReviewAssignment> */
    private array $items = [];

    public function save(VendorCatalogReviewAssignment $assignment): void
    {
        $this->items[] = $assignment;
    }

    /** @return list<VendorCatalogReviewAssignment> */
    public function all(): array
    {
        return $this->items;
    }
}
