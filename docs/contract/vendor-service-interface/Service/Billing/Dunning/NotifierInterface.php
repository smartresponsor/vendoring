<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Dunning;

interface NotifierInterface
{

    public function enqueue(string $invoiceId, string $template): bool;
}

