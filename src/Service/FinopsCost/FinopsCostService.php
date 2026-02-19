<?php declare(strict_types=1);
namespace App\Service\FinopsCost;
use App\ServiceInterface\FinopsCost\FinopsCostInterface;
final class FinopsCostService implements FinopsCostInterface {
    public function ok(): bool { return true; }
}
