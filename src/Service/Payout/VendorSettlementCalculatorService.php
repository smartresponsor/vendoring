<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Payout;

use App\Vendoring\DTO\Ledger\VendorLedgerAccountSumCriteriaDTO;
use App\Vendoring\RepositoryInterface\Vendor\VendorLedgerRepositoryInterface;
use App\Vendoring\ServiceInterface\Payout\VendorSettlementCalculatorServiceInterface;
use Doctrine\DBAL\Exception;

final readonly class VendorSettlementCalculatorService implements VendorSettlementCalculatorServiceInterface
{
    public function __construct(private VendorLedgerRepositoryInterface $ledger)
    {
    }

    /**
     * Simplified: net = debit(VENDOR_PAYABLE) - credit(VENDOR_PAYABLE) over period.
     *
     * @throws Exception
     */
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float
    {
        return max(0.0, $this->ledger->sumByAccount(new VendorLedgerAccountSumCriteriaDTO(
            tenantId: $tenantId,
            accountCode: 'VENDOR_PAYABLE',
            from: $from,
            to: $to,
            vendorId: $vendorId,
            currency: $currency,
        )));
    }
}
