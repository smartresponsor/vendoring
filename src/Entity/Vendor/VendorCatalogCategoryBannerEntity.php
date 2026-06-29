<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Objecting\EntityTrait\Embeddable\ObjectCodeEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectPublicationEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectTitleEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Canonical banner for a catalog category.
 *
 * object_code         → categoryId (the category this banner belongs to)
 * object_first_title  → title (banner headline)
 * object_middle_title → content (banner body text)
 * object_published    → published flag
 * object_published_at → publication timestamp
 *
 * Schema is stable: no JSON payload.
 * schema:update --force is safe on this entity.
 */
#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCatalogCategoryBannerRepository::class)]
#[ORM\Table(name: 'vendor_catalog_category_banner')]
class VendorCatalogCategoryBannerEntity extends VendorAbstractEntity
{
    use ObjectCodeEmbeddableTrait;
    use ObjectTitleEmbeddableTrait;
    use ObjectPublicationEmbeddableTrait;

    public function __construct(string $categoryId, string $title, string $content)
    {
        parent::__construct('active');
        $this->initializeObjectCode($categoryId);
        $this->initializeObjectTitle(firstTitle: $title, middleTitle: $content);
        $this->initializeObjectPublication();
    }

    public function categoryId(): string
    {
        return $this->getObjectCode() ?? '';
    }

    public function title(): string
    {
        return $this->getFirstTitle() ?? '';
    }

    public function content(): string
    {
        return $this->getMiddleTitle() ?? '';
    }

    public function published(): bool
    {
        return $this->isObjectPublished();
    }

    public function publish(): void
    {
        $this->publishObject();
    }

    public function id(): string
    {
        return (string) ($this->getId() ?? '');
    }
}
