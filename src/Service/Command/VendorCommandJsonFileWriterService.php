<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Command;

use App\Vendoring\Exception\Command\VendorCommandIoException;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonEncoderServiceInterface;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonFileWriterServiceInterface;
use JsonException;

final readonly class VendorCommandJsonFileWriterService implements VendorCommandJsonFileWriterServiceInterface
{
    public function __construct(
        private VendorCommandJsonEncoderServiceInterface $commandJsonEncoder,
    ) {}

    /**
     * @throws VendorCommandIoException
     */
    public function write(string $path, string $json): void
    {
        $normalizedPath = trim($path);

        if ('' === $normalizedPath) {
            throw new VendorCommandIoException('Output path cannot be empty.');
        }

        $dir = dirname($normalizedPath);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new VendorCommandIoException(sprintf('Unable to create directory: %s', $dir));
        }

        if (false === file_put_contents($normalizedPath, $json, LOCK_EX)) {
            throw new VendorCommandIoException(sprintf('Unable to write JSON file: %s', $normalizedPath));
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @throws VendorCommandIoException
     */
    public function writePayload(string $path, array $payload): void
    {
        try {
            $this->write($path, $this->commandJsonEncoder->encode($payload));
        } catch (JsonException $exception) {
            throw new VendorCommandIoException(sprintf('Unable to encode JSON payload for output path: %s', trim($path)), 0, $exception);
        }
    }
}
