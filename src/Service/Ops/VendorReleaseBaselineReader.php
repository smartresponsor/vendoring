<?php

declare(strict_types=1);

namespace App\Service\Ops;

use App\Projection\VendorReleaseBaselineView;
use App\ServiceInterface\Ops\VendorReleaseBaselineReaderInterface;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;

/**
 * Builds a calm release baseline snapshot on top of the aggregated runtime
 * status contour without changing business behavior.
 */
final class VendorReleaseBaselineReader implements VendorReleaseBaselineReaderInterface
{
    public function __construct(
        private readonly VendorRuntimeStatusViewBuilderInterface $runtimeStatusViewBuilder,
    ) {
    }

    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorReleaseBaselineView {
        $runtimeStatus = $this->runtimeStatusViewBuilder->build(
            tenantId: $tenantId,
            vendorId: $vendorId,
            from: $from,
            to: $to,
            currency: $currency,
        )->toArray();

        $projectRoot = dirname(__DIR__, 3);
        $artifactStatus = [
            'runtimeStatusCommand' => file_exists($projectRoot.'/src/Command/VendorRuntimeStatusCommand.php'),
            'runtimeStatusController' => file_exists($projectRoot.'/src/Controller/Ops/VendorRuntimeStatusController.php'),
            'runtimeStatusCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_RUNTIME_STATUS_CANON.md'),
            'ownerCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_OWNER_IDENTITY_CANON.md'),
            'userAssignmentCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_USER_ASSIGNMENT_CANON.md'),
            'apiKeyCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_API_KEY_CANON.md'),
            'securityStateCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_SECURITY_STATE_CANON.md'),
            'financeRuntimeCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_FINANCE_RUNTIME_CANON.md'),
            'statementDeliveryCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_STATEMENT_DELIVERY_RUNTIME_CANON.md'),
            'externalIntegrationCanon' => file_exists($projectRoot.'/docs/internal/VENDOR_EXTERNAL_INTEGRATION_RUNTIME_CANON.md'),
        ];

        $issues = [];
        $surfaceStatus = $runtimeStatus['surfaceStatus'];
        foreach ($surfaceStatus as $surface => $ready) {
            if (true !== $ready) {
                $issues[] = sprintf('surface.%s.missing', (string) $surface);
            }
        }
        foreach ($artifactStatus as $artifact => $present) {
            if (true !== $present) {
                $issues[] = sprintf('artifact.%s.missing', $artifact);
            }
        }

        $status = [] === $issues ? 'ok' : 'warn';

        return new VendorReleaseBaselineView(
            tenantId: $tenantId,
            vendorId: $vendorId,
            runtimeStatus: $runtimeStatus,
            artifactStatus: $artifactStatus,
            issues: $issues,
            status: $status,
            generatedAt: (new \DateTimeImmutable())->format(DATE_ATOM),
        );
    }
}
