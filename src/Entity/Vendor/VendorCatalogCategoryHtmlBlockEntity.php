<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Objecting\EntityTrait\Embeddable\ObjectCodeEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectPublicationEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectTitleEmbeddableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Canonical HTML block for a catalog category.
 *
 * object_code         → categoryId (the category this block belongs to)
 * object_middle_title → html (block HTML content)
 * object_published    → published flag
 * object_published_at → publication timestamp
 *
 * Schema is stable: no JSON payload.
 * schema:update --force is safe on this entity.
 */
#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorCatalogCategoryHtmlBlockRepository::class)]
#[ORM\Table(name: 'vendor_catalog_category_html_block')]
class VendorCatalogCategoryHtmlBlockEntity extends VendorAbstractEntity
{
    use ObjectCodeEmbeddableTrait;
    use ObjectTitleEmbeddableTrait;
    use ObjectPublicationEmbeddableTrait;

    public function __construct(string $categoryId, string $html)
    {
        parent::__construct('active');
        $this->initializeObjectCode($categoryId);
        $this->initializeObjectTitle(middleTitle: $html);
        $this->initializeObjectPublication();
    }

    public function categoryId(): string
    {
        return $this->getObjectCode() ?? '';
    }

    public function html(): string
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
