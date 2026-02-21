<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Ob\Trace;

interface TraceContextInterface
{

    public function propagate(array $headers): array;
}

