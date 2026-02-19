<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Worker;

interface SyncWorkerInterface
{

    public function __construct(private QueuePort $q, private callable $handler, private int $maxRetries = 5);

    public function runOnce(): void;
}
