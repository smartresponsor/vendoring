<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\EntityInterface\Vendor\Entity;

interface VendorInterface
{

    public function __construct(string $brandName, ?int $userId = null);

    public function getId(): int;

    public function getBrandName(): string;

    public function getUserId(): ?int;

    public function getStatus(): string;

    public function getCreatedAt(): \DateTimeImmutable;

    public function rename(string $brandName): void;

    public function activate(): void;

    public function deactivate(): void;
}
