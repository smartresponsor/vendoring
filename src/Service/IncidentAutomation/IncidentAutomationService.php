<?php declare(strict_types=1);
namespace App\Service\IncidentAutomation;
use App\ServiceInterface\IncidentAutomation\IncidentAutomationInterface;
final class IncidentAutomationService implements IncidentAutomationInterface {
    public function ok(): bool { return true; }
}
