<?php declare(strict_types=1);
namespace App\Service\ObservabilityV2;
use App\ServiceInterface\ObservabilityV2\ObservabilityV2Interface;
final class ObservabilityV2Service implements ObservabilityV2Interface {
    public function ok(): bool { return true; }
}
