<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;
use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class RuntimeLogger implements RuntimeLoggerInterface
{
    /**
     * @var list<array<string, scalar|null>>
     */
    private array $records = [];

    public function __construct(
        private readonly CorrelationContextInterface $correlationContext,
        private readonly RequestStack $requestStack,
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

    public function snapshot(): array
    {
        return $this->records;
    }

    /**
     * @param array<string, scalar|null> $context
     */
    private function write(string $level, string $message, array $context): void
    {
        $request = $this->requestStack->getCurrentRequest();

        $record = [
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'correlation_id' => $this->correlationContext->currentCorrelationId(),
            'route' => $this->routeName($request),
            'path' => $request?->getPathInfo(),
        ];

        foreach ($context as $key => $value) {
            $record[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }

        $this->records[] = $record;

        $environment = (string) ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'dev');
        if ('test' === $environment) {
            return;
        }

        $encoded = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (false !== $encoded) {
            error_log($encoded);
        }
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
