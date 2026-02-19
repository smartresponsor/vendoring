<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Queue;

interface SqliteQueueInterface
{

    public function __construct(private PDO $pdo);

    public function enqueue(string $topic, string $key, array $payload): void;

    public function reserve(int $limit = 1): array;

    public function ack(string $id): void;

    public function fail(string $id, string $reason): void;
}
