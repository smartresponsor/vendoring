<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

final readonly class VendorCategorySyndicationMappingProfileValueObject
{
    /**
     * @param array<string, string> $fieldMap
     * @param list<string>          $requiredFields
     */
    public function __construct(
        private string $destinationId,
        private string $version,
        private array  $fieldMap,
        private array  $requiredFields,
        private string $localeMode,
    ) {}

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
