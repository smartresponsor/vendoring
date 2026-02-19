<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Pricing;

interface PlanInterface
{

    public function __construct(public string $name, public array $quotas, public array $features);
}
