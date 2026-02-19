<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Payout;

interface VendorStatementControllerInterface
{

    public function __construct(private readonly VendorStatementService $svc);

    public function build(string $vendorId, Request $r): JsonResponse;
}
