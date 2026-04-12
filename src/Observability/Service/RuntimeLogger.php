<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;
use App\ServiceInterface\Observability\ObservabilityRecordExporterInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Structured runtime logger for request-scoped operational events.
 *
 * The logger builds a deterministic operational envelope that includes correlation,
 * route, path, and caller-supplied context fields. In non-test environments the
 * envelope is emitted as one JSON line via the PHP error log.
 */
final class RuntimeLogger implements RuntimeLoggerInterface
{
    /**
     * @var list<array<string, scalar|null>>
     */
    private array $records = [];

    public function __construct(
        private readonly CorrelationContextInterface $correlationContext,
        private readonly RequestStack $requestStack,
        private readonly ?ObservabilityRecordExporterInterface $exporter = null,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function snapshot(): array
    {
        return $this->records;
    }

    /**
     * Write one structured runtime record.
     *
     * @param array<string, scalar|null> $context Additional structured fields merged into the log envelope.
     */
    private function write(string $level, string $message, array $context): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $correlationId = $this->correlationContext->currentCorrelationId();

        /** @var array<string, scalar|null> $record */
        $record = [
            'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'request_id' => $correlationId,
            'correlation_id' => $correlationId,
            'route' => $this->routeName($request),
            'path' => $request?->getPathInfo(),
            'vendor_id' => null,
            'transaction_id' => null,
            'error_code' => null,
        ];

        foreach ($context as $key => $value) {
            $record[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }

        $this->records[] = $record;

        if ($this->exporter instanceof ObservabilityRecordExporterInterface) {
            $this->exporter->export('runtime_logs', $record);
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
     * Resolve the current Symfony route name from the active request.
     */
    private function routeName(?Request $request): ?string
    {
        if (!$request instanceof Request) {
            return null;
        }

        $route = $request->attributes->get('_route');

        return is_string($route) && '' !== trim($route) ? $route : null;
    }
}
