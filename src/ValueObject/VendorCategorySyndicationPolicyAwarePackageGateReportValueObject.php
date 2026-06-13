<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

final readonly class VendorCategorySyndicationPolicyAwarePackageGateReportValueObject
{
    /**
     * @param list<string>        $packageMissingRequiredFields
     * @param list<string>        $requiredMissing
     * @param list<string>        $warnings
     * @param array<string, bool> $checks
     * @param list<string>        $exactMatchedBindingIds
     * @param list<string>        $fallbackMatchedBindingIds
     */
    public function __construct(
        private string $mediaPolicyMode,
        private array  $packageMissingRequiredFields,
        private array  $requiredMissing,
        private array  $warnings,
        private array  $checks,
        private array  $exactMatchedBindingIds,
        private array  $fallbackMatchedBindingIds,
        private bool   $strictPublishable,
        private bool   $fallbackPublishable,
        private bool   $resolvedPublishable,
        private bool   $fallbackUsed,
    ) {}

    public function mediaPolicyMode(): string
    {
        return $this->mediaPolicyMode;
    }

    /** @return list<string> */
    public function packageMissingRequiredFields(): array
    {
        return $this->packageMissingRequiredFields;
    }

    /** @return list<string> */
    public function requiredMissing(): array
    {
        return $this->requiredMissing;
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

    public function resolvedPublishable(): bool
    {
        return $this->resolvedPublishable;
    }

    public function fallbackUsed(): bool
    {
        return $this->fallbackUsed;
    }
}
