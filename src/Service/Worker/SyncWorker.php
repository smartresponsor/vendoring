<?php
declare(strict_types = 1);

namespace App\Service\Worker;

use SmartResponsor\Vendor\Port\Queue\QueuePort;

final class SyncWorker
{
    private \Closure $handler;

    public function __construct(private QueuePort $q, callable $handler, private int $maxRetries = 5)
    {
        $this->handler = \Closure::fromCallable($handler);
    }

    public function runOnce(): void
    {
        foreach ($this->q->reserve(10) as $m) {
            try {
                ($this->handler)($m['topic'], $m['k'], json_decode($m['payload'], true, 512, JSON_THROW_ON_ERROR));
                $this->q->ack($m['id']);
            } catch (\Throwable $e) {
                if (((int)$m['tries']) >= $this->maxRetries) {
                    $this->q->fail($m['id'], $e->getMessage());
                }
            }
        }
    }
}
