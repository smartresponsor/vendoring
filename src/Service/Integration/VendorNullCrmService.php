<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Integration;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Exception\Integration\VendorCrmProviderNotConfiguredException;
use App\Vendoring\ServiceInterface\Integration\VendorCrmServiceInterface;

/**
 * Null-object CRM service for environments where no real CRM provider is configured.
 *
 * This service is the safe default bound in services.yaml.
 * It replaces the previous empty implementation that silently swallowed
 * registrations, causing invisible data drift between Vendoring and CRM systems.
 *
 * Behaviour: throws VendorCrmProviderNotConfiguredException immediately.
 * The calling layer is responsible for catching this as a non-critical
 * integration gap when CRM is genuinely optional in the current deployment.
 *
 * To activate a real provider:
 *   1. Implement VendorCrmServiceInterface (e.g. HubspotVendorCrmService).
 *   2. Re-bind VendorCrmServiceInterface in the host application services.yaml.
 */
final class VendorNullCrmService implements VendorCrmServiceInterface
{
    /**
     * @throws VendorCrmProviderNotConfiguredException always
     */
    public function registerVendor(VendorEntity $vendor): void
    {
        throw new VendorCrmProviderNotConfiguredException();
    }
}
