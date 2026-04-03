<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;
use App\ServiceInterface\Observability\MetricCollectorInterface;

final class RuntimeMetricCollector implements MetricCollectorInterface
{
    public function __construct(private readonly CorrelationContextInterface $correlationContext)
    {
    }

    public function increment(string $name, array $tags = []): void
    {
        $environment = (string) ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev');
        if ('test' === $environment) {
            return;
        }

        $record = [
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'type' => 'metric',
            'name' => $name,
            'tags' => $tags,
            'correlation_id' => $this->correlationContext->currentCorrelationId(),
        ];

        $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false !== $encoded) {
            error_log($encoded);
        }
    }
}
