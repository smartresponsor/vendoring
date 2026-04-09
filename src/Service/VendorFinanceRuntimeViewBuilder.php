<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\Projection\VendorFinanceRuntimeView;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use App\ServiceInterface\Metric\VendorMetricServiceInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;

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
    ) {
    }

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

        $metricOverview = $this->metricService->overview($tenantId, $vendorId, $from, $to, $currency);

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
                new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency)
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
