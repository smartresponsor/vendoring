<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Finance;

use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\Vendor\VendorFinanceRuntimeProjection;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use Doctrine\DBAL\Exception;

/**
 * Builds a finance-facing runtime summary that keeps vendor ownership/access
 * context adjacent to payout and statement surfaces.
 */
final readonly class VendorFinanceRuntimeProjectionBuilderService implements VendorFinanceRuntimeProjectionBuilderServiceInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderServiceInterface $ownershipProjectionBuilder,
        private VendorMetricServiceInterface $metricService,
        private VendorPayoutAccountRepositoryInterface $payoutAccountRepository,
        private VendorStatementServiceInterface $statementService,
    ) {}

    /**
     * @throws Exception
     */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorFinanceRuntimeProjection {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownershipProjection = $this->ownershipProjectionBuilder->buildForVendorId((int) $vendorId);
            $ownership = $ownershipProjection?->toArray();
        }

        $metricOverview = $this->metricService->overview(new VendorMetricOverviewRequestDTO(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from,
            to: $to,
            currency: $currency,
        ));

        $payoutAccountEntity = $this->payoutAccountRepository->get($tenantId, $vendorId);
        $payoutAccount = null;
        if (null !== $payoutAccountEntity) {
            $payoutAccount = [
                'provider' => $payoutAccountEntity->provider,
                'accountRef' => $payoutAccountEntity->accountRef,
                'currency' => $payoutAccountEntity->currency,
                'active' => $payoutAccountEntity->active,
                'createdAt' => $payoutAccountEntity->createdAt,
            ];
        }

        $statement = null;
        if (null !== $from && null !== $to && '' !== $from && '' !== $to) {
            $statement = $this->statementService->build(
                new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency),
            );
        }

        return new VendorFinanceRuntimeProjection(
            tenantId: $tenantId,
            vendorId: $vendorId,
            currency: $currency,
            ownership: $ownership,
            metricOverview: $metricOverview,
            payoutAccount: $payoutAccount,
            statement: $statement,
        );
    }
}
