<?php

declare(strict_types=1);

namespace App\Vendoring\Contract;

/**
 * Typed integration contract DTO for the Vendoring component.
 *
 * Describes what Vendoring owns and exposes to neighbouring components.
 * Administering knows ONLY this shape via the generic contract interface;
 * consumers that need typed access cast after registry->get('vendoring').
 *
 * Ownership surface
 * ─────────────────
 * Vendoring is the canonical owner of the vendor lifecycle:
 *   - vendor identity and profile
 *   - vendor capability/status and readiness state
 *   - vendor settlement prerequisites and payout readiness
 *   - vendor-facing operational metadata
 *
 * Integration seams
 * ─────────────────
 * Neighbouring components consume Vendoring exclusively through:
 *   - VendorOwnershipProjectionBuilderServiceInterface
 *   - VendorFinanceRuntimeProjectionBuilderServiceInterface
 *   - VendorTransactionLifecycleServiceInterface
 *   - VendorPayoutProviderServiceInterface
 *   - VendorStatementServiceInterface
 *   - VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface
 */
final readonly class VendoringIntegrationContract
{
    public function __construct(
        /** Domain area owned by this component. Canonical value: 'vendor_lifecycle'. */
        public string $owns,

        /** Subject prefix for Rolling/ACL decisions. Example: 'vendoring:vendor:' */
        public string $subjectPrefix,

        /** Permission prefix for Vendoring-scoped permissions. Example: 'vendoring.' */
        public string $permissionPrefix,

        /** Route-map key prefix used by Cruding. Example: 'vendor' */
        public string $routeMapPrefix,

        /** FQCN of the primary bundle class. */
        public string $bundleClass,

        /** FQCN of VendorOwnershipProjectionBuilderServiceInterface. */
        public string $ownershipProjectionBuilderInterface,

        /** FQCN of VendorTransactionLifecycleServiceInterface. */
        public string $transactionLifecycleInterface,

        /** FQCN of VendorPayoutProviderServiceInterface. */
        public string $payoutProviderInterface,

        /** FQCN of VendorStatementServiceInterface. */
        public string $statementInterface,

        /** FQCN of VendorFinanceRuntimeProjectionBuilderServiceInterface. */
        public string $financeRuntimeProjectionBuilderInterface,

        /** FQCN of VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface. */
        public string $externalIntegrationRuntimeProjectionBuilderInterface,

        /**
         * Runtime surfaces: surface slug => FQCN of canonical service interface.
         *
         * @var array<string, string>
         */
        public array $surfaces,
    ) {
    }
}
