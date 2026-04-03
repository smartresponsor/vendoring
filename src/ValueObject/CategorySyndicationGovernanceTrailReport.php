<?php

declare(strict_types=1);

namespace App\ValueObject;

final class CategorySyndicationGovernanceTrailReport
{
    /**
     * @param array<string, int>  $historyCounts
     * @param list<string>        $warnings
     * @param array<string, bool> $checks
     */
    public function __construct(
        private readonly string $destinationId,
        private readonly string $categoryId,
        private readonly string $mediaPolicyMode,
        private readonly bool $strictPublishable,
        private readonly bool $fallbackPublishable,
        private readonly bool $resolvedPublishable,
        private readonly bool $fallbackUsed,
        private readonly string $deliveryStatus,
        private readonly bool $retryable,
        private readonly bool $retryScheduled,
        private readonly array $historyCounts,
        private readonly array $warnings,
        private readonly array $checks,
    ) {
    }

    public function destinationId(): string
    {
        return $this->destinationId;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function mediaPolicyMode(): string
    {
        return $this->mediaPolicyMode;
    }

    public function strictPublishable(): bool
    {
        return $this->strictPublishable;
    }

    public function fallbackPublishable(): bool
    {
        return $this->fallbackPublishable;
    }

    public function resolvedPublishable(): bool
    {
        return $this->resolvedPublishable;
    }

    public function fallbackUsed(): bool
    {
        return $this->fallbackUsed;
    }

    public function deliveryStatus(): string
    {
        return $this->deliveryStatus;
    }

    public function retryable(): bool
    {
        return $this->retryable;
    }

    public function retryScheduled(): bool
    {
        return $this->retryScheduled;
    }

    /** @return array<string, int> */
    public function historyCounts(): array
    {
        return $this->historyCounts;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /** @return array<string, bool> */
    public function checks(): array
    {
        return $this->checks;
    }
}
