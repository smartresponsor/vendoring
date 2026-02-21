<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Governance;

interface ChangeLogInterface
{

    public function append(array $c): bool;
}

