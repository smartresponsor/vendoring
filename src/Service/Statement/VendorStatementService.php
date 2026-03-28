<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;

final class VendorStatementService implements VendorStatementServiceInterface
{
    private const ACCOUNT_REVENUE = 'REVENUE';
    private const ACCOUNT_REFUNDS_PAYABLE = 'REFUNDS_PAYABLE';
    private const CSV_SEPARATOR = ',';
    private const CSV_ENCLOSURE = '"';
    private const CSV_ESCAPE = '\\';
    private const CSV_HEADER = ['Section', 'Amount', 'Currency'];

    public function __construct(private readonly LedgerEntryRepositoryInterface $ledger)
    {
    }

    /**
     * @return array{tenantId:string, vendorId:string, from:string, to:string, currency:string, opening:float, earnings:float, refunds:float, fees:float, closing:float, items:list<array{type:string, amount:float, currency:string}>}
     */
    public function build(VendorStatementRequestDTO $dto): array
    {
        $opening = 0.0;
        $earnings = $this->sumPositive($dto, self::ACCOUNT_REVENUE);
        $refunds = $this->sumPositive($dto, self::ACCOUNT_REFUNDS_PAYABLE);
        $fees = 0.0;
        $closing = $earnings - $refunds - $fees;
        $items = [
            $this->buildItem('earnings', $earnings, $dto->currency),
            $this->buildItem('refunds', $refunds, $dto->currency),
            $this->buildItem('fees', $fees, $dto->currency),
        ];

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
        $handle = fopen($path, 'w');

        if (false === $handle) {
            throw new \RuntimeException(sprintf('Failed to open csv stream: %s', $path));
        }

        $this->writeCsvRow($handle, self::CSV_HEADER);

        foreach ($data['items'] as $item) {
            $this->writeCsvRow($handle, [$item['type'], $item['amount'], $item['currency']]);
        }

        $this->writeCsvRow($handle, []);
        $this->writeCsvRow($handle, ['Opening', $data['opening'], $dto->currency]);
        $this->writeCsvRow($handle, ['Closing', $data['closing'], $dto->currency]);
        fclose($handle);

        return $path;
    }

    private function sumPositive(VendorStatementRequestDTO $dto, string $account): float
    {
        return max(0.0, $this->ledger->sumByAccount(
            $dto->tenantId,
            $account,
            $dto->from,
            $dto->to,
            $dto->vendorId,
            $dto->currency,
        ));
    }

    /**
     * @return array{type:string, amount:float, currency:string}
     */
    private function buildItem(string $type, float $amount, string $currency): array
    {
        return [
            'type' => $type,
            'amount' => $amount,
            'currency' => $currency,
        ];
    }

    /**
     * @param list<float|int|string> $row
     */
    private function writeCsvRow($handle, array $row): void
    {
        fputcsv($handle, $row, self::CSV_SEPARATOR, self::CSV_ENCLOSURE, self::CSV_ESCAPE);
    }
}
