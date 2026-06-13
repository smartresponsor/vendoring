<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Catalog;

interface VendorCatalogMerchServiceInterface
{
    public function pinCreate(string $categoryId, string $recordId, int $position): void;

    public function pinDelete(string $categoryId, string $recordId): void;

    /**
     * @param list<string> $recordIds
     */
    public function orderSet(string $categoryId, array $recordIds): void;

    public function bannerPublish(string $categoryId, string $title, string $content): string;

    public function htmlPublish(string $categoryId, string $html): string;
}
