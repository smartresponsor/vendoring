<?php

declare(strict_types=1);

namespace App\ServiceInterface\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;

interface VendorStatementServiceInterface
{
    public function build(VendorStatementRequestDTO $dto): array;

    public function exportCsv(VendorStatementRequestDTO $dto): string;
}
