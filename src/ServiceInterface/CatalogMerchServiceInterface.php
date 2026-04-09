<?php

declare(strict_types=1);

namespace App\ServiceInterface;

/**
 * Application contract for catalog merch service operations.
 */
interface CatalogMerchServiceInterface
{
    /**
     * Executes the pin create operation for this runtime surface.
     */
    public function pinCreate(string $categoryId, string $recordId, int $position): void;

    /**
     * Executes the pin delete operation for this runtime surface.
     */
    public function pinDelete(string $categoryId, string $recordId): void;

    /**
     * @param list<string> $recordIds
     */
    public function orderSet(string $categoryId, array $recordIds): void;

    /**
     * Executes the banner publish operation for this runtime surface.
     */
    public function bannerPublish(string $categoryId, string $title, string $content): string;

    /**
     * Executes the html publish operation for this runtime surface.
     */
    public function htmlPublish(string $categoryId, string $html): string;
}
