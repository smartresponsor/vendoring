<?php

declare(strict_types=1);

namespace App\EntityInterface;

interface VendorUserAssignmentInterface
{
    public function getId(): ?int;

    public function getVendorId(): int;

    public function getUserId(): int;

    public function getRole(): string;

    public function getStatus(): string;

    public function isPrimary(): bool;

    public function getGrantedAt(): \DateTimeImmutable;

    public function getRevokedAt(): ?\DateTimeImmutable;
}
