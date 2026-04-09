<?php

declare(strict_types=1);

namespace App\ValueObject\Traffic;

final readonly class WriteRateLimitDecision
{
    public function __construct(
        private bool $allowed,
        private int $limit,
        private int $remaining,
        private int $retryAfterSeconds,
    ) {
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function remaining(): int
    {
        return $this->remaining;
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
