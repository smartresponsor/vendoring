<?php

declare(strict_types=1);

namespace App\Vendoring\Projection;

/**
 * Release-facing baseline view built from the current runtime status and
 * artifact presence checks after a green test contour.
 */
final readonly class VendorReleaseBaselineView
{
    /**
     * @param array<string,mixed> $runtimeStatus
     * @param array<string,bool>  $artifactStatus
     * @param list<string>        $issues
     */
    public function __construct(
        private string $tenantId,
        private string $vendorId,
        private array  $runtimeStatus,
        private array  $artifactStatus,
        private array  $issues,
        private string $status,
        private string $generatedAt,
    ) {}

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
