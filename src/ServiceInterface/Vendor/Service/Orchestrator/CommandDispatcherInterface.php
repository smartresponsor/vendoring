<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Orchestrator;

interface CommandDispatcherInterface
{

    public function __construct(private OrchestratorPort $o);

    public function renameVendor(string $id, string $newName): void;
}
