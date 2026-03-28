<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;

final class VendorStatementService implements VendorStatementServiceInterface
{
    private const CSV_SEPARATOR = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '\\';

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger)
    {
    }

    /**
     * @return array{tenantId:string, vendorId:string, from:string, to:string, currency:string, opening:float, earnings:float, refunds:float, fees:float, closing:float, items:list<array{type:string, amount:float, currency:string}>}
     */
    public function build(VendorStatementRequestDTO $dto): array
    {
        $opening = 0.0;
        $earnings = max(0.0, $this->ledger->sumByAccount($dto->tenantId, 'REVENUE', $dto->from, $dto->to, $dto->vendorId, $dto->currency));
        $refunds = max(0.0, $this->ledger->sumByAccount($dto->tenantId, 'REFUNDS_PAYABLE', $dto->from, $dto->to, $dto->vendorId, $dto->currency));
        $fees = 0.0;
        $closing = $earnings - $refunds - $fees;
        $items = [['type' => 'earnings', 'amount' => $earnings, 'currency' => $dto->currency], ['type' => 'refunds', 'amount' => $refunds, 'currency' => $dto->currency], ['type' => 'fees', 'amount' => $fees, 'currency' => $dto->currency]];

        return [
            'tenantId' => $dto->tenantId,
            'vendorId' => $dto->vendorId,
            'from' => $dto->from,
            'to' => $dto->to,
            'currency' => $dto->currency,
            'opening' => $opening,
            'earnings' => $earnings,
            'refunds' => $refunds,
            'fees' => $fees,
            'closing' => $closing,
            'items' => $items,
        ];
    }

    public function exportCsv(VendorStatementRequestDTO $dto): string
    {
        $data = $this->build($dto);
        $path = sys_get_temp_dir().'/statement_'.$dto->vendorId.'_'.date('YmdHis').'.csv';
        $f = fopen($path, 'w');

        if (false === $f) {
            throw new \RuntimeException(sprintf('Failed to open csv stream: %s', $path));
        }

        fputcsv($f, ['Section', 'Amount', 'Currency'], self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        foreach ($data['items'] as $row) {
            fputcsv($f, [$row['type'], $row['amount'], $row['currency']], self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        }
        fputcsv($f, [], self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        fputcsv($f, ['Opening', $data['opening'], $dto->currency], self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        fputcsv($f, ['Closing', $data['closing'], $dto->currency], self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
        fclose($f);

        return $path;
    }
}
