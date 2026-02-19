<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Version;

interface NegotiatorInterface
{

    public function choose(array $accepts): string;
}
