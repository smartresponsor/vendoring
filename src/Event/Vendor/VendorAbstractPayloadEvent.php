<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\EventInterface\Vendor\VendorPayloadEventInterface;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

abstract class VendorAbstractPayloadEvent extends Event implements VendorPayloadEventInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    final public function __construct(
        private readonly array $payload,
        private readonly DateTimeImmutable $occurredAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    final public function payload(): array
    {
        return $this->payload;
    }

    final public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
