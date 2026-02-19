<?php declare(strict_types=1);
namespace App\Service\DataImport;
use App\ServiceInterface\DataImport\DataImportInterface;
final class DataImportService implements DataImportInterface {
    public function ok(): bool { return true; }
}
