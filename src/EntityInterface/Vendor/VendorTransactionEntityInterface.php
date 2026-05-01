<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\EntityInterface\Vendor;

use DateTimeImmutable;

interface VendorTransactionEntityInterface
{
    public function getId(): ?int;

    public function getVendorId(): string;

    public function getOrderId(): string;

    public function getProjectId(): ?string;

    public function getAmount(): string;

    public function getStatus(): string;

    public function getCreatedAt(): DateTimeImmutable;
}
