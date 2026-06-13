<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Command;

use App\Vendoring\Exception\Command\VendorCommandIoException;

interface VendorCommandJsonArtifactWriterServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     * @throws VendorCommandIoException
     */
    public function writeIfRequested(bool $shouldWrite, mixed $outputOption, string $defaultPath, array $payload): ?string;
}
