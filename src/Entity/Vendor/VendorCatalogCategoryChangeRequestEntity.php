<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_change_request')]
#[ORM\Index(name: 'idx_vendor_catalog_category_change_request_category', columns: ['category_id', 'status'])]
final class VendorCatalogCategoryChangeRequestEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 96)]
    private string $id;

    #[ORM\Column(name: 'category_id', type: 'string', length: 96)]
    private string $categoryId;

    #[ORM\Column(name: 'submitter_id', type: 'string', length: 96)]
    private string $submitterId;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(type: 'string', length: 255)]
    private string $reason;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(string $id, string $categoryId, string $submitterId, string $reason, array $payload)
    {
        $this->id = trim($id);
        $this->categoryId = trim($categoryId);
        $this->submitterId = trim($submitterId);
        $this->reason = trim($reason);
        $this->payload = $payload;
        $this->status = 'open';
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function open(string $id, string $categoryId, string $submitterId, string $reason, array $payload): self
    {
        return new self($id, $categoryId, $submitterId, $reason, $payload);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function submitterId(): string
    {
        return $this->submitterId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
