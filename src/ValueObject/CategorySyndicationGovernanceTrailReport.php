<?php

declare(strict_types=1);

namespace App\ValueObject;

final readonly class CategorySyndicationGovernanceTrailReport
{
    /**
     * @param array<string, int>  $historyCounts
     * @param list<string>        $warnings
     * @param array<string, bool> $checks
     */
    public function __construct(
        private string $destinationId,
        private string $categoryId,
        private string $mediaPolicyMode,
        private bool $strictPublishable,
        private bool $fallbackPublishable,
        private bool $resolvedPublishable,
        private bool $fallbackUsed,
        private string $deliveryStatus,
        private bool $retryable,
        private bool $retryScheduled,
        private array $historyCounts,
        private array $warnings,
        private array $checks,
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
