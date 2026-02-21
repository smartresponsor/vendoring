<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Audit;

interface InMemoryAuditPortInterface
{

    public function append(string $actor, string $action, string $target, string $result): void;
}

