<?php declare(strict_types=1);
namespace App\Service\TenantSelfservice;
use App\ServiceInterface\TenantSelfservice\TenantSelfserviceInterface;
final class TenantSelfserviceService implements TenantSelfserviceInterface {
    public function ok(): bool { return true; }
}
