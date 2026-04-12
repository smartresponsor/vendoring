<?php

declare(strict_types=1);

namespace App\Service\Ops;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Projection\VendorRuntimeStatusView;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;

/**
 * Builds a release-facing vendor runtime status view that aggregates existing
 * vendor-local surfaces into one ops/admin-friendly payload.
 */
final readonly class VendorRuntimeStatusViewBuilder implements VendorRuntimeStatusViewBuilderInterface
{
    public function __construct(
        private VendorOwnershipViewBuilderInterface                  $ownershipViewBuilder,
        private VendorFinanceRuntimeViewBuilderInterface             $financeRuntimeViewBuilder,
        private VendorStatementDeliveryRuntimeViewBuilderInterface   $statementDeliveryRuntimeViewBuilder,
        private VendorExternalIntegrationRuntimeViewBuilderInterface $externalIntegrationRuntimeViewBuilder,
    ) {}

    /** @throws Exception */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorRuntimeStatusView {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownership = $this->ownershipViewBuilder->buildForVendorId((int) $vendorId)?->toArray();
        }

        $finance = $this->financeRuntimeViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        )->toArray();

        $statementDelivery = $this->statementDeliveryRuntimeViewBuilder->build(new VendorStatementDeliveryRuntimeRequestDTO(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from ?? '',
            to: $to ?? '',
            currency: $currency,
        ))->toArray();

        $externalIntegration = $this->externalIntegrationRuntimeViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
        )->toArray();

        $statement = $statementDelivery['statement'];
        $export = $statementDelivery['export'];
        $recipients = $statementDelivery['recipients'];

        $surfaceStatus = [
            'ownership' => null !== $ownership,
            'finance' => [] !== $finance,
            'statementDelivery' => [] !== $statement || null !== $export || [] !== $recipients,
            'externalIntegration' => [] !== $externalIntegration,
        ];

        $generatedAtObject = new DateTimeImmutable();
        $generatedAt = $generatedAtObject->format(DATE_ATOM);

        return new VendorRuntimeStatusView(
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
