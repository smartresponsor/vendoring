<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Controller\Metric;

interface VendorMetricsControllerInterface
{

    public function __construct(private readonly VendorMetricsService $svc);

    public function overview(string $vendorId, Request $r): JsonResponse;

    public function trends(string $vendorId, Request $r): JsonResponse;
}
