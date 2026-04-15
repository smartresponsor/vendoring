<?php

declare(strict_types=1);

namespace App\Tests\Support\Observability;

use App\ServiceInterface\Observability\RuntimeLoggerInterface;
use DateTimeImmutable;

final class InMemoryRuntimeLogger implements RuntimeLoggerInterface
{
    /**
     * @var list<array<string, scalar|null>>
     */
    private array $records = [];

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
        $record = [
            'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
            'level' => $level,
            'message' => $message,
            'request_id' => null,
            'correlation_id' => null,
            'route' => null,
            'path' => null,
            'vendor_id' => null,
            'transaction_id' => null,
            'error_code' => null,
        ];

        foreach ($context as $key => $value) {
            $record[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
        }

        $this->records[] = $record;
    }
}
