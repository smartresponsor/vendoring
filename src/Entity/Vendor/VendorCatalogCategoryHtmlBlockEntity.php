<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_html_block')]
#[ORM\Index(name: 'idx_vendor_catalog_category_html_block_category', columns: ['category_id', 'status'])]
final class VendorCatalogCategoryHtmlBlockEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 96)]
    private string $categoryId;

    #[ORM\Column(type: 'text')]
    private string $html;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'draft';

    #[ORM\Column(name: 'published_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;

    public function __construct(string $categoryId, string $html)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->categoryId = trim($categoryId);
        $this->html = trim($html);
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

    public function status(): string
    {
        return $this->status;
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->publishedAt = new DateTimeImmutable();
    }

    public function published(): bool
    {
        return 'published' === $this->status;
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
