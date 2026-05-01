<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Catalog;

interface VendorCatalogReviewAssignmentServiceInterface
{
    /**
     * @return array<string, string>
     */
    public function assign(string $requestId, string $reviewer, string $assignedBy, ?string $priority = null): array;
}
