<?php

declare(strict_types=1);

namespace App\Entity;

final class VendorCatalogCategoryChangeRequest
{
    private string $status;

    /**
     * @param array<string, mixed> $payload
     */
    private function __construct(
        private readonly string $id,
        private readonly string $categoryId,
        private readonly string $submittedBy,
        private readonly string $title,
        private readonly array $payload,
    ) {
        $this->status = 'open';
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function open(string $id, string $categoryId, string $submittedBy, string $title, array $payload): self
    {
        return new self($id, $categoryId, $submittedBy, $title, $payload);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function submittedBy(): string
    {
        return $this->submittedBy;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
