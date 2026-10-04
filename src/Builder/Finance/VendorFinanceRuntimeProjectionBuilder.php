<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Finance;

use App\Vendoring\BuilderInterface\Finance\VendorFinanceRuntimeProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\VendorFinanceRuntimeProjection;
use App\Vendoring\RepositoryInterface\VendorPayoutAccountRepositoryInterface;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use Doctrine\DBAL\Exception;

/**
 * Builds a finance-facing runtime summary that keeps vendor ownership/access
 * context adjacent to payout and statement surfaces.
 */
final readonly class VendorFinanceRuntimeProjectionBuilder implements VendorFinanceRuntimeProjectionBuilderInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderInterface $ownershipProjectionBuilder,
        private VendorMetricServiceInterface $metricService,
        private VendorPayoutAccountRepositoryInterface $payoutAccountRepository,
        private VendorStatementServiceInterface $statementService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function build(
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
            vendorId: $vendorId,
            from: $from,
            to: $to,
            currency: $currency,
        ));

        $payoutAccountEntity = $this->payoutAccountRepository->get($vendorId);
        $payoutAccount = null;
        if (null !== $payoutAccountEntity) {
            $payoutAccount = [
                'provider' => $payoutAccountEntity->provider,
                'accountRef' => $payoutAccountEntity->accountRef,
                'currency' => $payoutAccountEntity->currency,
                'active' => $payoutAccountEntity->active,
                'createdAt' => $payoutAccountEntity->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $statement = null;
        if (null !== $from && null !== $to && '' !== $from && '' !== $to) {
            $statement = $this->statementService->build(
                new VendorStatementRequestDTO($vendorId, $from, $to, $currency),
            );
        }

        return new VendorFinanceRuntimeProjection(
            vendorId: $vendorId,
            currency: $currency,
            ownership: $ownership,
            metricOverview: $metricOverview,
            payoutAccount: $payoutAccount,
            statement: $statement,
        );
    }
}
