<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Process;

final class LocalDevPantherTest extends PantherTestCase
{
    private const SERVER_HOST = '127.0.0.1';
    private static int $serverPort;

    public static function setUpBeforeClass(): void
    {
        self::$serverPort = self::allocatePort();

        self::runServerCommand('HOST='.self::SERVER_HOST.' PORT='.self::$serverPort.' ./tools/local/server-start.sh');
    }

    public static function tearDownAfterClass(): void
    {
        self::runServerCommand('HOST='.self::SERVER_HOST.' PORT='.self::$serverPort.' ./tools/local/server-stop.sh');
    }

    public function testHomePageAndHealthEndpointLoadInChromium(): void
    {
        $client = self::createPantherClient([
            'browser' => PantherTestCase::CHROME,
            'external_base_uri' => sprintf('http://%s:%d', self::SERVER_HOST, self::$serverPort),
        ], [], ['port' => self::allocatePort()]);

        $client->request('GET', '/');
        self::assertPageTitleContains('Vendoring Local Dev');
        self::assertSelectorTextContains('h1', 'Vendoring Local Dev');
        self::assertSelectorTextContains('body', 'Local runtime is up.');

        $client->request('GET', '/healthz');
        self::assertSelectorTextContains('body', '"status":"ok"');
    }

    protected function tearDown(): void
    {
        self::restoreExceptionHandlerStack();
    }

    private static function runServerCommand(string $command): void
    {
        $process = Process::fromShellCommandline($command, dirname(__DIR__, 2));
        $process->mustRun();
    }

    private static function allocatePort(): int
    {
        $socket = @stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

        if (false === $socket) {
            throw new \RuntimeException(sprintf('Failed to allocate port: %s (%d)', $errorMessage, $errorCode));
        }

        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        if (!is_string($name) || !preg_match('/:(\d+)$/', $name, $matches)) {
            throw new \RuntimeException('Failed to resolve allocated port.');
        }

        return (int) $matches[1];
    }

    private static function restoreExceptionHandlerStack(): void
    {
        for ($attempt = 0; $attempt < 32; ++$attempt) {
            $currentHandler = set_exception_handler(static function (\Throwable $throwable): void {
                throw $throwable;
            });

            restore_exception_handler();

            if (null === $currentHandler) {
                return;
            }

            restore_exception_handler();
        }
    }
}
