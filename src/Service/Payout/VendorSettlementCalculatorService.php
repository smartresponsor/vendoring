<?php

declare(strict_types=1);

namespace App\Service\Payout;

use Doctrine\DBAL\Exception;
use App\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Payout\VendorSettlementCalculatorServiceInterface;

final readonly class VendorSettlementCalculatorService implements VendorSettlementCalculatorServiceInterface
{
    public function __construct(private LedgerEntryRepositoryInterface $ledger) {}

    /**
     * Simplified: net = debit(VENDOR_PAYABLE) - credit(VENDOR_PAYABLE) over period.
     *
     * @throws Exception
     */
    public function netForPeriod(string $tenantId, string $vendorId, string $from, string $to, string $currency): float
    {
        return max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $tenantId,
            accountCode: 'VENDOR_PAYABLE',
            from: $from,
            to: $to,
            vendorId: $vendorId,
            currency: $currency,
        )));
    }
}
