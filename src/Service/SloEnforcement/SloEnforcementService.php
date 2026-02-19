<?php declare(strict_types=1);
namespace App\Service\SloEnforcement;
use App\ServiceInterface\SloEnforcement\SloEnforcementInterface;
final class SloEnforcementService implements SloEnforcementInterface {
    public function ok(): bool { return true; }
}
