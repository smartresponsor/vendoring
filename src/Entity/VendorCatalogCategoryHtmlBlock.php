<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_html_block')]
final class VendorCatalogCategoryHtmlBlock
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 255)]
    private readonly string $categoryId;

    #[ORM\Column(type: 'text')]
    private readonly string $html;

    #[ORM\Column(type: 'boolean')]
    private bool $published = false;

    public function __construct(string $categoryId, string $html)
    {
        $this->categoryId = $categoryId;
        $this->html = $html;
        $this->id = sha1($categoryId . '|' . $html);
    }

    public function publish(): void
    {
        $this->published = true;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function html(): string
    {
        return $this->html;
    }

    public function published(): bool
    {
        return $this->published;
    }
}
