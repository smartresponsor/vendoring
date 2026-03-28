<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Process;

final class LocalDevPantherTest extends PantherTestCase
{
    private const SERVER_HOST = '127.0.0.1';
    private const HOME_PAGE_TITLE = 'Vendoring Local Dev';
    private const HOME_PAGE_HEADING = 'Vendoring Local Dev';
    private const HOME_PAGE_BODY = 'Local runtime is up.';
    private const HEALTH_RESPONSE_FRAGMENT = '"status":"ok"';
    private const CHROMEDRIVER_BINARY = 'drivers/chromedriver';
    private static int $serverPort;

    public static function setUpBeforeClass(): void
    {
        self::configurePantherEnvironment();
        self::$serverPort = self::allocatePort();

        self::runServerCommand('./tools/local/server-start.sh');
    }

    public static function tearDownAfterClass(): void
    {
        self::runServerCommand('./tools/local/server-stop.sh');
    }

    public function testHomePageAndHealthEndpointLoadInChromium(): void
    {
        self::requireChromeDriver();
        $client = $this->createLocalDevClient();

        try {
            $client->request('GET', '/');
            self::assertPageTitleContains(self::HOME_PAGE_TITLE);
            self::assertSelectorTextContains('h1', self::HOME_PAGE_HEADING);
            self::assertSelectorTextContains('body', self::HOME_PAGE_BODY);

            $client->request('GET', '/healthz');
            self::assertSelectorTextContains('body', self::HEALTH_RESPONSE_FRAGMENT);
        } finally {
            self::quitClient($client);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::restoreExceptionHandlerStack();
    }

    private function createLocalDevClient(): Client
    {
        return self::createPantherClient([
            'browser' => PantherTestCase::CHROME,
            'external_base_uri' => self::serverBaseUri(),
        ], [], ['port' => self::allocatePort()]);
    }

    private static function runServerCommand(string $command): void
    {
        $process = Process::fromShellCommandline($command, self::projectRoot(), [
            'HOST' => self::SERVER_HOST,
            'PORT' => (string) self::$serverPort,
        ]);
        $process->mustRun();
    }

    private static function serverBaseUri(): string
    {
        return sprintf('http://%s:%d', self::SERVER_HOST, self::$serverPort);
    }

    private static function configurePantherEnvironment(): void
    {
        self::setPantherEnv('PANTHER_NO_SANDBOX', '1');
        self::prependPath(self::projectRoot().'/drivers');
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

    private static function setPantherEnv(string $name, string $value): void
    {
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    private static function prependPath(string $directory): void
    {
        $path = getenv('PATH');
        $normalizedPath = is_string($path) && '' !== $path ? $directory.PATH_SEPARATOR.$path : $directory;

        self::setPantherEnv('PATH', $normalizedPath);
    }

    private static function requireChromeDriver(): void
    {
        $process = new Process([self::projectRoot().'/'.self::CHROMEDRIVER_BINARY, '--version'], self::projectRoot());
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        self::markTestSkipped('chromedriver is not available on this host');
    }

    private static function quitClient(Client $client): void
    {
        try {
            $client->quit();
        } catch (\Throwable) {
            // Panther may already have torn down chromedriver on shutdown.
        }
    }

    private static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
