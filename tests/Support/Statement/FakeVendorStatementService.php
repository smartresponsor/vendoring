<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;

final class FakeVendorStatementService implements VendorStatementServiceInterface
{
    /** @var array<string,mixed> */
    private array $response;

    /** @var list<VendorStatementRequestDTO> */
    private array $requests = [];

    /** @param array<string,mixed> $response */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function build(VendorStatementRequestDTO $dto): array
    {
        $this->requests[] = $dto;

        return $this->response;
    }

    public function exportCsv(VendorStatementRequestDTO $dto): string
    {
        $this->requests[] = $dto;

        return sys_get_temp_dir().'/fake-statement.csv';
    }

    /** @return list<VendorStatementRequestDTO> */
    public function requests(): array
    {
        return $this->requests;
    }
}
