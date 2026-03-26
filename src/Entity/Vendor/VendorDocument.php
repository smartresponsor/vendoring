<?php

declare(strict_types=1);

namespace App\Entity\Vendor;

final class VendorDocument
{
    private ?int $id = null;
    private ?\DateTimeImmutable $expiresAt = null;
    private ?int $uploaderId = null;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $type,
        private readonly string $filePath,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUploaderId(): ?int
    {
        return $this->uploaderId;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
