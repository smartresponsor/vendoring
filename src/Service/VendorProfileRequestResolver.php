<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\VendorProfileDTO;
use App\ServiceInterface\VendorProfileRequestResolverInterface;
use InvalidArgumentException;

final class VendorProfileRequestResolver implements VendorProfileRequestResolverInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function resolve(int $vendorId, array $payload): VendorProfileDTO
    {
        $socials = $payload['socials'] ?? null;

        if (null !== $socials && !is_array($socials)) {
            throw new InvalidArgumentException('socials_must_be_object');
        }

        return new VendorProfileDTO(
            vendorId: $vendorId,
            displayName: $this->nullableString($payload, 'displayName'),
            about: $this->nullableString($payload, 'about'),
            website: $this->nullableString($payload, 'website'),
            socials: $this->normalizeSocials($socials),
            seoTitle: $this->nullableString($payload, 'seoTitle'),
            seoDescription: $this->nullableString($payload, 'seoDescription'),
            publicationAction: $this->nullableString($payload, 'publicationAction'),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function nullableString(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;

        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('%s_must_be_string', $field));
        }

        return (string) $value;
    }

    /**
     * @param array<array-key, mixed>|null $socials
     * @return array<string, string>|null
     */
    private function normalizeSocials(?array $socials): ?array
    {
        if (null === $socials) {
            return null;
        }

        $normalized = [];

        foreach ($socials as $network => $url) {
            if (!is_scalar($url)) {
                throw new InvalidArgumentException('socials_must_be_object');
            }

            $normalized[(string) $network] = (string) $url;
        }

        return $normalized;
    }
}
