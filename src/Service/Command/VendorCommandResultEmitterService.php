<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Command;

use App\Vendoring\Enum\Command\VendorCommandOutputFormatEnum;
use App\Vendoring\ServiceInterface\Command\VendorCommandJsonEncoderServiceInterface;
use App\Vendoring\ServiceInterface\Command\VendorCommandResultEmitterServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final readonly class VendorCommandResultEmitterService implements VendorCommandResultEmitterServiceInterface
{
    public function __construct(
        private VendorCommandJsonEncoderServiceInterface $commandJsonEncoder,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function emitJson(OutputInterface $output, array $payload): bool
    {
        try {
            $output->writeln($this->commandJsonEncoder->encode($payload));
        } catch (\JsonException) {
            $output->writeln('{"status":"failed","error":"json_encode_failed"}');

            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function emitError(OutputInterface $output, string $format, string $status, string $message, array $context = []): bool
    {
        if (VendorCommandOutputFormatEnum::isJson($format)) {
            return $this->emitJson($output, [
                'status' => $status,
                'error' => $message,
                ...$context,
            ]);
        }

        $output->writeln(sprintf('<error>%s</error>', $message));

        return true;
    }

    /**
     * @noinspection PhpTooManyParametersInspection
     * @param array<string, mixed> $context
     */
    public function emitThrowableError(OutputInterface $output, string $format, string $status, string $prefix, Throwable $throwable, array $context = []): bool
    {
        $message = '' !== trim($throwable->getMessage())
            ? sprintf('%s: %s', $prefix, $throwable->getMessage())
            : $prefix;

        return $this->emitError($output, $format, $status, $message, [
            ...$context,
            'errorClass' => $throwable::class,
        ]);
    }
}
