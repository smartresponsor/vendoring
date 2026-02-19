<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Statement;

use App\DTO\Vendor\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Vendor\Statement\VendorStatementServiceInterface;
use App\RepositoryInterface\Vendor\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;

final class VendorStatementService implements VendorStatementServiceInterface
{
    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger)
    {
    }

    public function build(VendorStatementRequestDTO $dto): array
    {
        $opening = 0.0;
        $earnings = max(0.0, $this->ledger->sumByAccount($dto->tenantId, 'REVENUE'));
        $refunds = max(0.0, $this->ledger->sumByAccount($dto->tenantId, 'REFUNDS_PAYABLE'));
        $fees = 0.0;
        $closing = $earnings - $refunds - $fees;
        $items = [['type' => 'earnings', 'amount' => $earnings, 'currency' => $dto->currency], ['type' => 'refunds', 'amount' => $refunds, 'currency' => $dto->currency], ['type' => 'fees', 'amount' => $fees, 'currency' => $dto->currency]];
        return ['opening' => $opening, 'earnings' => $earnings, 'refunds' => $refunds, 'fees' => $fees, 'closing' => $closing, 'items' => $items];
    }

    public function exportCsv(VendorStatementRequestDTO $dto): string
    {
        $data = $this->build($dto);
        $path = sys_get_temp_dir() . '/statement_' . $dto->vendorId . '_' . date('YmdHis') . '.csv';
        $f = fopen($path, 'w');
        fputcsv($f, ['Section', 'Amount', 'Currency']);
        foreach ($data['items'] as $row) fputcsv($f, [$row['type'], $row['amount'], $row['currency']]);
        fputcsv($f, []);
        fputcsv($f, ['Opening', $data['opening'], $dto->currency]);
        fputcsv($f, ['Closing', $data['closing'], $dto->currency]);
        fclose($f);
        return $path;
    }
}
