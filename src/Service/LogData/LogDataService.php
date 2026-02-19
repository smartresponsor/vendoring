<?php declare(strict_types=1);
namespace App\Service\LogData;
use App\ServiceInterface\LogData\LogDataInterface;
final class LogDataService implements LogDataInterface {
    public function ok(): bool { return true; }
}
