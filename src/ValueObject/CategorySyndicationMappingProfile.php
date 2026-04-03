<?php

declare(strict_types=1);

namespace App\ValueObject;

final class CategorySyndicationMappingProfile
{
    /**
     * @param array<string, string> $fieldMap
     * @param list<string>          $requiredFields
     */
    public function __construct(
        private readonly string $destinationId,
        private readonly string $version,
        private readonly array $fieldMap,
        private readonly array $requiredFields,
        private readonly string $localeMode,
    ) {
    }

    public function destinationId(): string
    {
        return $this->destinationId;
    }

    public function version(): string
    {
        return $this->version;
    }

    /**
     * @return array<string, string>
     */
    public function fieldMap(): array
    {
        return $this->fieldMap;
    }

    /**
     * @return list<string>
     */
    public function requiredFields(): array
    {
        return $this->requiredFields;
    }

    public function localeMode(): string
    {
        return $this->localeMode;
    }
}
