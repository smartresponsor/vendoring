<?php

declare(strict_types=1);

namespace App\ValueObject;

final class CategorySyndicationPublishPackage
{
    /**
     * @param array<string, mixed> $payload
     * @param list<string>         $missingRequiredFields
     */
    public function __construct(
        private readonly string $packageId,
        private readonly string $destinationId,
        private readonly string $categoryId,
        private readonly string $version,
        private readonly string $localeMode,
        private readonly array $payload,
        private readonly array $missingRequiredFields,
        private readonly bool $publishable,
    ) {
    }

    public function packageId(): string
    {
        return $this->packageId;
    }

    public function destinationId(): string
    {
        return $this->destinationId;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function localeMode(): string
    {
        return $this->localeMode;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @return list<string>
     */
    public function missingRequiredFields(): array
    {
        return $this->missingRequiredFields;
    }

    public function publishable(): bool
    {
        return $this->publishable;
    }
}
