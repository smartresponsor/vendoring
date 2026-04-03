<?php

declare(strict_types=1);

namespace App\ValueObject\Traffic;

final class WriteRateLimitDecision
{
    public function __construct(
        private readonly bool $allowed,
        private readonly int $limit,
        private readonly int $remaining,
        private readonly int $retryAfterSeconds,
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
