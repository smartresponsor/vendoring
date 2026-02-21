<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Webhook;

interface DlqInterface
{

    public function enqueue(array $evt): bool;
}

