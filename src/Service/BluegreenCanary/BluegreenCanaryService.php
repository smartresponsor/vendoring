<?php declare(strict_types=1);
namespace App\Service\BluegreenCanary;
use App\ServiceInterface\BluegreenCanary\BluegreenCanaryInterface;
final class BluegreenCanaryService implements BluegreenCanaryInterface {
    public function ok(): bool { return true; }
}
