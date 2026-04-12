<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;

final class FakeVendorStatementService implements VendorStatementServiceInterface
{
    /**
     * @var array{
     *   tenantId:string,
     *   vendorId:string,
     *   from:string,
     *   to:string,
     *   currency:string,
     *   opening:float,
     *   earnings:float,
     *   refunds:float,
     *   fees:float,
     *   closing:float,
     *   items:list<array{type:string, amount:float, currency:string}>
     * }
     */
    private array $response;

    /** @var list<VendorStatementRequestDTO> */
    private array $requests = [];

    /** @param array{tenantId?:string, vendorId?:string, from?:string, to?:string, currency?:string, opening?:float|int, earnings?:float|int, refunds?:float|int, fees?:float|int, closing?:float|int, balance?:float|int, items?:list<array{type:string, amount:float|int, currency:string}>} $response */
    public function __construct(array $response)
    {
        $items = [];
        foreach ($response['items'] ?? [] as $item) {
            $items[] = [
                'type' => (string) $item['type'],
                'amount' => (float) $item['amount'],
                'currency' => (string) $item['currency'],
            ];
        }

        $this->response = [
            'tenantId' => $response['tenantId'] ?? 'tenant-test',
            'vendorId' => $response['vendorId'] ?? 'vendor-test',
            'from' => $response['from'] ?? '',
            'to' => $response['to'] ?? '',
            'currency' => $response['currency'] ?? 'USD',
            'opening' => (float) ($response['opening'] ?? 0.0),
            'earnings' => (float) ($response['earnings'] ?? 0.0),
            'refunds' => (float) ($response['refunds'] ?? 0.0),
            'fees' => (float) ($response['fees'] ?? 0.0),
            'closing' => (float) ($response['closing'] ?? ($response['balance'] ?? 0.0)),
            'items' => $items,
        ];
    }

    public function build(VendorStatementRequestDTO $dto): array
    {
        $this->requests[] = $dto;

        return $this->response;
    }

    public function exportCsv(VendorStatementRequestDTO $dto): string
    {
        $this->requests[] = $dto;

        return sys_get_temp_dir() . '/fake-statement.csv';
    }

    /** @return list<VendorStatementRequestDTO> */
    public function requests(): array
    {
        return $this->requests;
    }
}
