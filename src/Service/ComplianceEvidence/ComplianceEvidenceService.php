<?php declare(strict_types=1);
namespace App\Service\ComplianceEvidence;
use App\ServiceInterface\ComplianceEvidence\ComplianceEvidenceInterface;
final class ComplianceEvidenceService implements ComplianceEvidenceInterface {
    public function ok(): bool { return true; }
}
