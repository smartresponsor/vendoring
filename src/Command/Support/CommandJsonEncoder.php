<?php

declare(strict_types=1);

namespace App\Command\Support;

use JsonException;

final class CommandJsonEncoder
{
    /**
     * @param array<string, mixed> $payload
     * @throws JsonException
     */
    public function encode(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
