<?php
declare(strict_types = 1);

namespace App\Service\Orchestrator;

use SmartResponsor\Vendor\Port\Orchestrator\OrchestratorPort;

final class CommandDispatcher
{
    public function __construct(private OrchestratorPort $o)
    {
    }

    public function renameVendor(string $id, string $newName): void
    {
        $this->o->dispatch('vendor.rename', ['id' => $id, 'name' => $newName], 'vendor.rename.' . $id);
    }
}
