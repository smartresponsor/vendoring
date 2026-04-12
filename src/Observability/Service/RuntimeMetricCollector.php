<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;
use App\ServiceInterface\Observability\MetricCollectorInterface;
use App\ServiceInterface\Observability\ObservabilityRecordExporterInterface;
use DateTimeImmutable;

/**
 * Structured metric collector for runtime observability events.
 *
 * The collector emits deterministic metric envelopes that can later be forwarded to
 * external backends. In non-test environments the envelope is written as JSON to the
 * PHP error log; in every environment it is retained in-memory for inspection.
 */
final class RuntimeMetricCollector implements MetricCollectorInterface
{
    /**
     * @var list<array{
     *   'timestamp': string,
     *   'type': string,
     *   'name': string,
     *   'tags': array<string, string>,
     *   'request_id': ?string,
     *   'correlation_id': ?string
     * }>
     */
    private array $records = [];

    public function __construct(
        private readonly CorrelationContextInterface $correlationContext,
        private readonly ?ObservabilityRecordExporterInterface $exporter = null,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function increment(string $name, array $tags = []): void
    {
        $correlationId = $this->correlationContext->currentCorrelationId();

        /** @var array{'timestamp': string,'type': string,'name': string,'tags': array<string, string>,'request_id': ?string,'correlation_id': ?string} $record */
        $record = [
            'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
            'type' => 'metric',
            'name' => $name,
            'tags' => $this->normalizeTags($tags),
            'request_id' => $correlationId,
            'correlation_id' => $correlationId,
        ];

        $this->records[] = $record;

        if ($this->exporter instanceof ObservabilityRecordExporterInterface) {
            $this->exporter->export('runtime_metrics', $record);
        }

        $environment = (string) ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev');
        if ('test' === $environment) {
            return;
        }

        $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false !== $encoded) {
            error_log($encoded);
        }
    }

    /**
     * @return list<array{
     *   'timestamp': string,
     *   'type': string,
     *   'name': string,
     *   'tags': array<string, string>,
     *   'request_id': ?string,
     *   'correlation_id': ?string
     * }>
     */
    public function snapshot(): array
    {
        return $this->records;
    }

    /**
     * @param array<string, string> $tags
     * @return array<string, string>
     */
    private function normalizeTags(array $tags): array
    {
        return array_map(static fn(mixed $value): string => (string) $value, $tags);
    }
}
