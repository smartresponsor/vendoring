<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\DTO\Metric\VendorMetricOverviewRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\VendorFinanceRuntimeView;
use App\Vendoring\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\Vendoring\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\Vendoring\ServiceInterface\VendorOwnershipViewBuilderInterface;
use Doctrine\DBAL\Exception;

/**
 * Builds a finance-facing runtime summary that keeps vendor ownership/access
 * context adjacent to payout and statement surfaces.
 */
final readonly class VendorFinanceRuntimeViewBuilder implements VendorFinanceRuntimeViewBuilderInterface
{
    public function __construct(
        private VendorOwnershipViewBuilderInterface $ownershipViewBuilder,
        private VendorMetricServiceInterface $metricService,
        private PayoutAccountRepositoryInterface $payoutAccountRepository,
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
    ): VendorFinanceRuntimeView {
        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownershipView = $this->ownershipViewBuilder->buildForVendorId((int) $vendorId);
            $ownership = $ownershipView?->toArray();
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

        return new VendorFinanceRuntimeView(
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
