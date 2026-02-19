<?php declare(strict_types=1);
namespace App\Service\SchemaMigration;
use App\ServiceInterface\SchemaMigration\SchemaMigrationInterface;
final class SchemaMigrationService implements SchemaMigrationInterface {
    public function ok(): bool { return true; }
}
