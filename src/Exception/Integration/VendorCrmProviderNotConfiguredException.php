<?php

declare(strict_types=1);

namespace App\Vendoring\Exception\Integration;

/**
 * Thrown when CRM registration is attempted but no real CRM provider is configured.
 *
 * A no-op implementation allows vendors to be created without CRM registration,
 * which leads to invisible data drift between systems. This exception makes
 * the missing configuration explicit at runtime.
 */
final class VendorCrmProviderNotConfiguredException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'CRM provider is not configured. '
            .'Bind a concrete implementation of VendorCrmServiceInterface '
            .'or explicitly suppress registration by configuring a NullCrmService.',
        );
    }
}
