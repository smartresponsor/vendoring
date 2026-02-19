<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Orchestrator;

interface QueueOrchestratorAdapterInterface
{

    public function __construct(private PDO $pdo);

    public function dispatch(string $name, array $payload, string $key): void;
}
