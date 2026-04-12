<?php

declare(strict_types=1);

namespace App\Tests\Support\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;

final class FakeStatementExporterPDF implements StatementExporterPDFInterface
{
    public function __construct(private readonly string $path) {}

    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string
    {
        return $this->path;
    }
}
