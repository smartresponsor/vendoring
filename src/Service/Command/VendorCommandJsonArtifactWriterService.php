<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Command;

use App\Vendoring\Exception\Command\VendorCommandIoException;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonArtifactWriterServiceInterface;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonFileWriterServiceInterface;

final readonly class VendorCommandJsonArtifactWriterService implements VendorCommandJsonArtifactWriterServiceInterface
{
    public function __construct(
        private VendorCommandJsonFileWriterServiceInterface $commandJsonFileWriter,
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @throws VendorCommandIoException
     */
    public function writeIfRequested(bool $shouldWrite, mixed $outputOption, string $defaultPath, array $payload): ?string
    {
        if (!$shouldWrite) {
            return null;
        }

        $outputPath = is_scalar($outputOption) && '' !== trim((string) $outputOption)
            ? trim((string) $outputOption)
            : $defaultPath;

        $this->commandJsonFileWriter->writePayload($outputPath, $payload);

        return $outputPath;
    }
}
