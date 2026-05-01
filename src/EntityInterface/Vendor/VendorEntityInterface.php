<?php

declare(strict_types=1);

namespace App\Vendoring\EntityInterface\Vendor;

use DateTimeImmutable;

interface VendorEntityInterface
{
    public function getId(): ?int;

    public function getBrandName(): string;

    public function getOwnerUserId(): ?int;

    public function getStatus(): string;

    public function getCreatedAt(): DateTimeImmutable;
}
