<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Payout;

interface PayoutAccountControllerInterface
{

    public function __construct(private readonly PayoutAccountRepository $repo);

    public function upsert(Request $r): JsonResponse;
}
