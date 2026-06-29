<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Observability;

use App\Vendoring\ServiceInterface\Observability\VendorCorrelationContextServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorObservabilityRecordExporterServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorRuntimeLoggerServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Structured runtime logger backed by a PSR-3 / Monolog channel.
 *
 * Replaces the previous error_log() implementation which:
 *   - bypassed Symfony's Monolog channel routing
 *   - had no handler, formatter, or processor support
 *   - could not be integrated with Sentry, ELK, or OpenTelemetry without raw log parsing
 *
 * The logger still maintains an in-memory snapshot() for test inspection and
 * forwards every record to the observability exporter stream when one is injected.
 *
 * Symfony DI wiring (services.yaml):
 *
 *   App\Vendoring\Service\Observability\VendorRuntimeLoggerService:
 *       arguments:
 *           $logger: '@monolog.logger.vendoring'
 *
 * monolog.yaml:
 *
 *   monolog:
 *       channels: [vendoring]
 *       handlers:
 *           vendoring:
 *               type: stream
 *               path: '%kernel.logs_dir%/vendoring.log'
 *               level: info
 *               channels: [vendoring]
 */
final class VendorRuntimeLoggerService implements VendorRuntimeLoggerServiceInterface
{
    /** @var list<array<string, scalar|null>> */
    private array $records = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly VendorCorrelationContextServiceInterface $correlationContext,
        private readonly RequestStack $requestStack,
        private readonly ?VendorObservabilityRecordExporterServiceInterface $exporter = null,
    ) {
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('error', $message, $context);
    }

    /** @return list<array<string, scalar|null>> */
    public function snapshot(): array
    {
        return $this->records;
    }

    /** @param array<string, scalar|null> $context */
    private function write(string $level, string $message, array $context): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $correlationId = $this->correlationContext->currentCorrelationId();

        /** @var array<string, scalar|null> $record */
        $record = [
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
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

        if ($this->exporter instanceof VendorObservabilityRecordExporterServiceInterface) {
            $this->exporter->export('runtime_logs', $record);
        }

        // Route through Monolog — supports handlers, formatters, processors,
        // channel routing, Sentry, ELK, OpenTelemetry out of the box.
        $logContext = array_filter(
            $record,
            static fn (mixed $v): bool => null !== $v,
        );
        unset($logContext['timestamp'], $logContext['level'], $logContext['message']);

        match ($level) {
            'error' => $this->logger->error($message, $logContext),
            'warning' => $this->logger->warning($message, $logContext),
            default => $this->logger->info($message, $logContext),
        };
    }

    private function routeName(?Request $request): ?string
    {
        if (!$request instanceof Request) {
            return null;
        }

        $route = $request->attributes->get('_route');

        return is_string($route) && '' !== trim($route) ? $route : null;
    }
}
