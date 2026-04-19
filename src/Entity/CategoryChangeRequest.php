<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

final class CategoryChangeRequest
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function open(string $id, string $categoryId, string $submittedBy, string $title, array $payload): VendorCatalogCategoryChangeRequest
    {
        return VendorCatalogCategoryChangeRequest::open($id, $categoryId, $submittedBy, $title, $payload);
    }
}
