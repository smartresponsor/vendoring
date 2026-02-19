<?php declare(strict_types=1);
namespace App\Service\SecurityHardening;
use App\ServiceInterface\SecurityHardening\SecurityHardeningInterface;
final class SecurityHardeningService implements SecurityHardeningInterface {
    public function ok(): bool { return true; }
}
