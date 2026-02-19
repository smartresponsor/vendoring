<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Money;

interface MoneyInterface
{

    public function __construct(public string $currency, public int $amount);

    public function add(self $other): self;

    public function sub(self $other): self;
}
