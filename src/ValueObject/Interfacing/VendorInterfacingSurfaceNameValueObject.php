<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject\Interfacing;

/**
 * Noun-based Vendoring presentation surfaces used for Interfacing template lookup.
 *
 * Physical template folders must use the business/surface noun "vendor". The component nameEntity
 * "vendoring" is intentionally not part of the canonical template path.
 */
final readonly class VendorInterfacingSurfaceNameValueObject
{
    public const INDEX = 'vendor.index';
    public const PROFILE = 'vendor.profile';

    /**
     * @var list<string>
     */
    private const SUPPORTED = [
        self::INDEX,
        self::PROFILE,
    ];

    public function __construct(public string $value)
    {
        if (!in_array($value, self::SUPPORTED, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported vendor Interfacing surface "%s".', $value));
        }
    }
}
