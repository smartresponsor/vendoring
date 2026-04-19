<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorCatalogCategoryBanner
{
    private string $id;
    private bool $published = false;

    public function __construct(string $categoryId, string $title, string $content)
    {
        $this->id = sha1($categoryId . '|' . $title . '|' . $content);
    }

    public function publish(): void
    {
        $this->published = true;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function published(): bool
    {
        return $this->published;
    }
}
