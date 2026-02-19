<?php declare(strict_types=1);
namespace App\Service\AuditlogTamper;
use App\ServiceInterface\AuditlogTamper\AuditlogTamperInterface;
final class AuditlogTamperService implements AuditlogTamperInterface {
    public function ok(): bool { return true; }
}
