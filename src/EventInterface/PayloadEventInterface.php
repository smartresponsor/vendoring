<?php

declare(strict_types=1);

namespace App\EventInterface;

interface PayloadEventInterface
{
    /**
     * @return array<string, mixed>
     */
    public function payload(): array;

    public function occurredAt(): \DateTimeImmutable;
}
