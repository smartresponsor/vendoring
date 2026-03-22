<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Release-facing baseline view built from the current runtime status and
 * artifact presence checks after a green test contour.
 */
final class VendorReleaseBaselineView
{
    /**
     * @param array<string,mixed> $runtimeStatus
     * @param array<string,bool>  $artifactStatus
     * @param list<string>        $issues
     */
    public function __construct(
        private readonly string $tenantId,
        private readonly string $vendorId,
        private readonly array $runtimeStatus,
        private readonly array $artifactStatus,
        private readonly array $issues,
        private readonly string $status,
        private readonly string $generatedAt,
    ) {
    }

    /**
     * @return array{
     *   tenantId:string,
     *   vendorId:string,
     *   runtimeStatus:array<string,mixed>,
     *   artifactStatus:array<string,bool>,
     *   issues:list<string>,
     *   status:string,
     *   generatedAt:string
     * }
     */
    public function toArray(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'vendorId' => $this->vendorId,
            'runtimeStatus' => $this->runtimeStatus,
            'artifactStatus' => $this->artifactStatus,
            'issues' => $this->issues,
            'status' => $this->status,
            'generatedAt' => $this->generatedAt,
        ];
    }
}
