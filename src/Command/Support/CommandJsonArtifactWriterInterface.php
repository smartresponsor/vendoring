<?php

declare(strict_types=1);

namespace App\Vendoring\Command\Support;

interface CommandJsonArtifactWriterInterface
{
    /**
     * @param array<string, mixed> $payload
     * @throws CommandIoException
     */
    public function writeIfRequested(bool $shouldWrite, mixed $outputOption, string $defaultPath, array $payload): ?string;
}
