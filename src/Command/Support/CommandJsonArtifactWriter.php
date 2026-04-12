<?php

declare(strict_types=1);

namespace App\Command\Support;

final readonly class CommandJsonArtifactWriter implements CommandJsonArtifactWriterInterface
{
    public function __construct(
        private CommandJsonFileWriter $commandJsonFileWriter,
    ) {}

    /**
     * @param array<string, mixed> $payload
     * @throws CommandIoException
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
