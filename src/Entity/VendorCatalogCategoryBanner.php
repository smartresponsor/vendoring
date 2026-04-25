<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_banner')]
final class VendorCatalogCategoryBanner
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 40)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 255)]
    private readonly string $categoryId;

    #[ORM\Column(type: 'string', length: 255)]
    private readonly string $title;

    #[ORM\Column(type: 'text')]
    private readonly string $content;

    #[ORM\Column(type: 'boolean')]
    private bool $published = false;

    public function __construct(string $categoryId, string $title, string $content)
    {
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->content = $content;
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

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function published(): bool
    {
        return $this->published;
    }
}
