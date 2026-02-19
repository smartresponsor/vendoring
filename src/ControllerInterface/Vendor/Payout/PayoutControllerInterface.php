<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ControllerInterface\Vendor\Payout;

interface PayoutControllerInterface
{

    public function __construct(private readonly PayoutService $svc, private readonly PayoutRepository $repo);

    public function create(Request $r): JsonResponse;

    public function process(string $payoutId): JsonResponse;

    public function getOne(string $payoutId): JsonResponse;
}
