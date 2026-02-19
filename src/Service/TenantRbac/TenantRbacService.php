<?php declare(strict_types=1);
namespace App\Service\TenantRbac;
use App\ServiceInterface\TenantRbac\TenantRbacInterface;
final class TenantRbacService implements TenantRbacInterface {
    public function ok(): bool { return true; }
}
