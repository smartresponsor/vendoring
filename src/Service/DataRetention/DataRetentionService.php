<?php declare(strict_types=1);
namespace App\Service\DataRetention;
use App\ServiceInterface\DataRetention\DataRetentionInterface;
final class DataRetentionService implements DataRetentionInterface {
    public function ok(): bool { return true; }
}
