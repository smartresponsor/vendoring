<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\VendorCatalogReviewAssignment;

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
