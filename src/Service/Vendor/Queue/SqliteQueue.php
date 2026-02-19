<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Queue;

use PDO;
use SmartResponsor\Vendor\Port\Queue\QueuePort;

final class SqliteQueue implements QueuePort
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS queue(id TEXT PRIMARY KEY, topic TEXT, k TEXT, payload TEXT, tries INTEGER DEFAULT 0, failed INTEGER DEFAULT 0, reserved INTEGER DEFAULT 0)");
    }

    public function enqueue(string $topic, string $key, array $payload): void
    {
        $id = $topic . ':' . $key;
        $st = $this->pdo->prepare("INSERT OR IGNORE INTO queue(id,topic,k,payload) VALUES(:id,:t,:k,:p)");
        $st->execute([':id' => $id, ':t' => $topic, ':k' => $key, ':p' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    }

    public function reserve(int $limit = 1): array
    {
        $this->pdo->beginTransaction();
        $res = $this->pdo->query("SELECT id,topic,k,payload,tries FROM queue WHERE reserved=0 AND failed=0 LIMIT " . (int)$limit)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $row) {
            $st = $this->pdo->prepare("UPDATE queue SET reserved=1, tries=tries+1 WHERE id=:id AND reserved=0");
            $st->execute([':id' => $row['id']]);
        }
        $this->pdo->commit();
        return $res;
    }

    public function ack(string $id): void
    {
        $this->pdo->prepare("DELETE FROM queue WHERE id=:id")->execute([':id' => $id]);
    }

    public function fail(string $id, string $reason): void
    {
        $this->pdo->prepare("UPDATE queue SET failed=1 WHERE id=:id")->execute([':id' => $id]);
    }
}
