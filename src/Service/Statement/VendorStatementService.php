<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\DTO\Ledger\LedgerAccountSumCriteriaDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use Doctrine\DBAL\Exception;
use RuntimeException;

final readonly class VendorStatementService implements VendorStatementServiceInterface
{
    public function __construct(private LedgerEntryRepositoryInterface $ledger) {}

    /**
     * @return array{tenantId:string, vendorId:string, from:string, to:string, currency:string, opening:float, earnings:float, refunds:float, fees:float, closing:float, items:list<array{type:string, amount:float, currency:string}>}
     * @throws Exception
     */
    public function build(VendorStatementRequestDTO $dto): array
    {
        $opening = 0.0;
        $earnings = max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $dto->tenantId,
            accountCode: 'REVENUE',
            from: $dto->from,
            to: $dto->to,
            vendorId: $dto->vendorId,
            currency: $dto->currency,
        )));
        $refunds = max(0.0, $this->ledger->sumByAccount(new LedgerAccountSumCriteriaDTO(
            tenantId: $dto->tenantId,
            accountCode: 'REFUNDS_PAYABLE',
            from: $dto->from,
            to: $dto->to,
            vendorId: $dto->vendorId,
            currency: $dto->currency,
        )));
        $fees = 0.0;
        $closing = $earnings - $refunds - $fees;
        $items = [
            ['type' => 'earnings', 'amount' => $earnings, 'currency' => $dto->currency],
            ['type' => 'refunds', 'amount' => $refunds, 'currency' => $dto->currency],
            ['type' => 'fees', 'amount' => $fees, 'currency' => $dto->currency],
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

    /** @throws Exception */
    public function exportCsv(VendorStatementRequestDTO $dto): string
    {
        $data = $this->build($dto);
        $path = sys_get_temp_dir() . '/statement_' . $dto->vendorId . '_' . date('YmdHis') . '.csv';
        $stream = fopen($path, 'w');

        if (false === $stream) {
            throw new RuntimeException(sprintf('Failed to open csv stream: %s', $path));
        }

        fputcsv($stream, ['Section', 'Amount', 'Currency']);
        foreach ($data['items'] as $row) {
            fputcsv($stream, [$row['type'], $row['amount'], $row['currency']]);
        }
        fputcsv($stream, []);
        fputcsv($stream, ['Opening', $data['opening'], $dto->currency]);
        fputcsv($stream, ['Closing', $data['closing'], $dto->currency]);
        fclose($stream);

        return $path;
    }
}
