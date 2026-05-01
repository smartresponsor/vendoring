<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Command;

use App\Vendoring\Exception\Command\VendorCommandIoException;

interface VendorCommandJsonFileWriterServiceInterface
{
    /**
     * @throws VendorCommandIoException
     */
    public function write(string $path, string $json): void;

    /**
     * @param array<string, mixed> $payload
     * @throws VendorCommandIoException
     */
    public function writePayload(string $path, array $payload): void;
}
