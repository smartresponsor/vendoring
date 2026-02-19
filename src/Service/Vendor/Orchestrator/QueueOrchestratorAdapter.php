<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Orchestrator;

use PDO;
use SmartResponsor\Vendor\Port\Orchestrator\OrchestratorPort;

final class QueueOrchestratorAdapter implements OrchestratorPort
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS orchestrator_queue(id TEXT PRIMARY KEY, name TEXT, payload TEXT)");
    }

    public function dispatch(string $name, array $payload, string $key): void
    {
        $st = $this->pdo->prepare("INSERT OR IGNORE INTO orchestrator_queue(id,name,payload) VALUES(:id,:n,:p)");
        $st->execute([':id' => $key, ':n' => $name, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    }
}
