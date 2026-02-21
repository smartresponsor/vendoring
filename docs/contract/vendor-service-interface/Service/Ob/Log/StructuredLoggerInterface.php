<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Ob\Log;

interface StructuredLoggerInterface
{

    public function log(string $level, string $msg, array $ctx = []): array;
}

