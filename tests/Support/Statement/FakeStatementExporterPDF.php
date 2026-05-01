<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Support\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPDFServiceInterface;

final class FakeStatementExporterPDF implements VendorStatementExporterPDFServiceInterface
{
    public function __construct(private readonly string $path) {}

    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string
    {
        return $this->path;
    }
}
