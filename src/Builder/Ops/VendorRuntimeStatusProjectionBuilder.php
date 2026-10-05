<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Ops;

use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ops\VendorRuntimeStatusProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\Projection\VendorRuntimeStatusProjection;
use Doctrine\DBAL\Exception;

/**
 * Builds a release-facing vendor runtime status projection that aggregates existing
 * vendor-local surfaces into one ops/admin-friendly payload.
 */
final readonly class VendorRuntimeStatusProjectionBuilder implements VendorRuntimeStatusProjectionBuilderInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderInterface $ownershipProjectionBuilder,
        private VendorFinanceRuntimeProjectionBuilderInterface $financeRuntimeProjectionBuilder,
        private VendorStatementDeliveryRuntimeProjectionBuilderInterface $statementDeliveryRuntimeProjectionBuilder,
        private VendorExternalIntegrationRuntimeProjectionBuilderInterface $externalIntegrationRuntimeProjectionBuilder,
    ) {
    }

    /** @throws Exception */
    public function build(
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
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        )->toArray();

        $statementDelivery = $this->statementDeliveryRuntimeProjectionBuilder->build(new VendorStatementDeliveryRuntimeRequestDTO(
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        ))->toArray();

        $externalIntegration = $this->externalIntegrationRuntimeProjectionBuilder->build(
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

        $generatedAtObject = new \DateTimeImmutable();
        $generatedAt = $generatedAtObject->format(DATE_ATOM);

        return new VendorRuntimeStatusProjection(
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
