<?php

declare(strict_types=1);

namespace App\Command\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

interface CommandResultEmitterInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function emitJson(OutputInterface $output, array $payload): bool;

    /**
     * @param array<string, mixed> $context
     */
    public function emitError(OutputInterface $output, string $format, string $status, string $message, array $context = []): bool;

    /**
     * @noinspection PhpTooManyParametersInspection
     * @param array<string, mixed> $context
     */
    public function emitThrowableError(OutputInterface $output, string $format, string $status, string $prefix, Throwable $throwable, array $context = []): bool;
}
