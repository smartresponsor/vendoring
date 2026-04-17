<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAttachment
{
    /** @var int|null */
    // @phpstan-ignore-next-line
    private ?int $id = null;
    private DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $title,
        private readonly string $filePath,
        private readonly ?string $category = null,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
