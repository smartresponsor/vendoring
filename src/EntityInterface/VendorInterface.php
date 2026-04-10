<?php

declare(strict_types=1);

namespace App\EntityInterface;

use DateTimeImmutable;

interface VendorInterface
{
    public function getId(): ?int;

    public function getBrandName(): string;

    public function getOwnerUserId(): ?int;

    public function getStatus(): string;

    public function getCreatedAt(): DateTimeImmutable;
}
