<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_banner')]
#[ORM\Index(name: 'idx_vendor_catalog_category_banner_category', columns: ['category_id', 'status'])]
final class VendorCatalogCategoryBannerEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 96)]
    private string $categoryId;

    #[ORM\Column(type: 'string', length: 160)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'draft';

    #[ORM\Column(name: 'published_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;

    public function __construct(string $categoryId, string $title, string $content)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->categoryId = trim($categoryId);
        $this->title = trim($title);
        $this->content = trim($content);
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

    public function status(): string
    {
        return $this->status;
    }

    public function publish(): void
    {
        $this->status = 'published';
        $this->publishedAt = new DateTimeImmutable();
    }

    public function publishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
