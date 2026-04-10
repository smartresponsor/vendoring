<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\ObservabilityRecordExporterInterface;
use RuntimeException;

/**
 * File-backed observability exporter for runtime logs and metrics.
 *
 * The exporter writes one JSON line per record into stable stream files under the
 * configured observability directory. This is a deployment-safe backend seam that can
 * later be replaced or complemented by Prometheus, OpenTelemetry, or StatsD adapters.
 */
final readonly class FileObservabilityRecordExporter implements ObservabilityRecordExporterInterface
{
    public function __construct(private string $observabilityDir)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function export(string $stream, array $payload): void
    {
        $normalizedStream = $this->normalizeStream($stream);
        $this->ensureDirectoryExists();

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false === $encoded) {
            return;
        }

        file_put_contents($this->streamPath($normalizedStream), $encoded.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Return the stable file path for one logical observability stream.
     */
    public function streamPath(string $stream): string
    {
        return rtrim($this->observabilityDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$stream.'.ndjson';
    }

    /**
     * Normalize the logical stream name to a filesystem-safe token.
     */
    private function normalizeStream(string $stream): string
    {
        $normalized = preg_replace('/[^a-z0-9_\-]+/i', '_', trim($stream));
        $normalized = is_string($normalized) ? trim($normalized, '_') : '';

        return '' !== $normalized ? strtolower($normalized) : 'observability';
    }

    /**
     * Ensure the export directory exists before appending records.
     */
    private function ensureDirectoryExists(): void
    {
        if (is_dir($this->observabilityDir)) {
            return;
        }

        mkdir($concurrentDirectory = $this->observabilityDir, 0777, true);
        if (!is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Observability directory "%s" could not be created.', $this->observabilityDir));
        }
    }
}
