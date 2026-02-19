<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Outbox;

interface SqliteOutboxInterface
{

    public function __construct(private PDO $pdo);

    public function publish(string $type, array $payload, string $key): void;
}
