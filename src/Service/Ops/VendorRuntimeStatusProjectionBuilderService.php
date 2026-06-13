<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ops;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\Vendor\VendorRuntimeStatusProjection;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;

/**
 * Builds a release-facing vendor runtime status projection that aggregates existing
 * vendor-local surfaces into one ops/admin-friendly payload.
 */
final readonly class VendorRuntimeStatusProjectionBuilderService implements VendorRuntimeStatusProjectionBuilderServiceInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderServiceInterface                  $ownershipProjectionBuilder,
        private VendorFinanceRuntimeProjectionBuilderServiceInterface             $financeRuntimeProjectionBuilder,
        private VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface   $statementDeliveryRuntimeProjectionBuilder,
        private VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface $externalIntegrationRuntimeProjectionBuilder,
    ) {}

    /** @throws Exception */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorRuntimeStatusProjection {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownership = $this->ownershipProjectionBuilder->buildForVendorId((int) $vendorId)?->toArray();
        }

        $finance = $this->financeRuntimeProjectionBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        )->toArray();

        $statementDelivery = $this->statementDeliveryRuntimeProjectionBuilder->build(new VendorStatementDeliveryRuntimeRequestDTO(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        ))->toArray();

        $externalIntegration = $this->externalIntegrationRuntimeProjectionBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
        )->toArray();

        $statement = $statementDelivery['statement'];
        $export = $statementDelivery['export'];
        $recipients = $statementDelivery['recipients'];

        $surfaceStatus = [
            'ownership' => null !== $ownership,
            'finance' => true,
            'statementDelivery' => [] !== $statement || null !== $export || [] !== $recipients,
            'externalIntegration' => true,
        ];

        $generatedAtObject = new DateTimeImmutable();
        $generatedAt = $generatedAtObject->format(DATE_ATOM);

        return new VendorRuntimeStatusProjection(
            tenantId: $tenantId,
            vendorId: $vendorId,
            currency: $currency,
            ownership: $ownership,
            finance: $finance,
            statementDelivery: $statementDelivery,
            externalIntegration: $externalIntegration,
            surfaceStatus: $surfaceStatus,
            generatedAt: $generatedAt,
        );
    }
}
