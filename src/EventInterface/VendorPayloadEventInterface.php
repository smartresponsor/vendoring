<?php

declare(strict_types=1);

namespace App\Vendoring\EventInterface;

interface VendorPayloadEventInterface
{
    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    public function occurredAt(): \DateTimeImmutable;
}
