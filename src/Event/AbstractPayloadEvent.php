<?php

declare(strict_types=1);

namespace App\Event;

use App\EventInterface\PayloadEventInterface;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractPayloadEvent extends Event implements PayloadEventInterface
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
