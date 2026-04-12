<?php

declare(strict_types=1);

namespace App\ValueObject;

final readonly class CategorySyndicationFallbackAwarePackageGateReport
{
    /**
     * @param list<string>        $packageMissingRequiredFields
     * @param list<string>        $strictMediaRequiredMissing
     * @param list<string>        $fallbackMediaRequiredMissing
     * @param list<string>        $warnings
     * @param array<string, bool> $checks
     * @param list<string>        $exactMatchedBindingIds
     * @param list<string>        $fallbackMatchedBindingIds
     */
    public function __construct(
        private array $packageMissingRequiredFields,
        private array $strictMediaRequiredMissing,
        private array $fallbackMediaRequiredMissing,
        private array $warnings,
        private array $checks,
        private array $exactMatchedBindingIds,
        private array $fallbackMatchedBindingIds,
        private bool  $strictPublishable,
        private bool  $fallbackPublishable,
    ) {}

    /** @return list<string> */
    public function packageMissingRequiredFields(): array
    {
        return $this->packageMissingRequiredFields;
    }

    /** @return list<string> */
    public function strictMediaRequiredMissing(): array
    {
        return $this->strictMediaRequiredMissing;
    }

    /** @return list<string> */
    public function fallbackMediaRequiredMissing(): array
    {
        return $this->fallbackMediaRequiredMissing;
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

    /** @return list<string> */
    public function exactMatchedBindingIds(): array
    {
        return $this->exactMatchedBindingIds;
    }

    /** @return list<string> */
    public function fallbackMatchedBindingIds(): array
    {
        return $this->fallbackMatchedBindingIds;
    }

    public function strictPublishable(): bool
    {
        return $this->strictPublishable;
    }

    public function fallbackPublishable(): bool
    {
        return $this->fallbackPublishable;
    }
}
