<?php declare(strict_types=1);
namespace App\Service\ApiRate;
use App\ServiceInterface\ApiRate\ApiRateInterface;
final class ApiRateService implements ApiRateInterface {
    public function ok(): bool { return true; }
}
