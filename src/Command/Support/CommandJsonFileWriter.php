<?php

declare(strict_types=1);

namespace App\Command\Support;

use JsonException;

final readonly class CommandJsonFileWriter
{
    public function __construct(
        private CommandJsonEncoder $commandJsonEncoder,
    ) {}

    /**
     * @throws CommandIoException
     */
    public function write(string $path, string $json): void
    {
        $normalizedPath = trim($path);

        if ('' === $normalizedPath) {
            throw new CommandIoException('Output path cannot be empty.');
        }

        $dir = dirname($normalizedPath);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new CommandIoException(sprintf('Unable to create directory: %s', $dir));
        }

        if (false === file_put_contents($normalizedPath, $json, LOCK_EX)) {
            throw new CommandIoException(sprintf('Unable to write JSON file: %s', $normalizedPath));
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @throws CommandIoException
     */
    public function writePayload(string $path, array $payload): void
    {
        try {
            $this->write($path, $this->commandJsonEncoder->encode($payload));
        } catch (JsonException $exception) {
            throw new CommandIoException(sprintf('Unable to encode JSON payload for output path: %s', trim($path)), 0, $exception);
        }
    }
}
