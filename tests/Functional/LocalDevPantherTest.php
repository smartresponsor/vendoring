<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Functional;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Process;

final class LocalDevPantherTest extends PantherTestCase
{
    private const SERVER_HOST = '127.0.0.1';
    private const SERVER_START_SCRIPT = 'tools/local/server-start.sh';
    private const SERVER_STOP_SCRIPT = 'tools/local/server-stop.sh';
    private const CANONICAL_TRANSACTION_CREATE_PATH = '/api/vendor/transaction';
    private const METHOD_NOT_ALLOWED_STATUS = 405;
    private const CHROMEDRIVER_SKIP_MESSAGE = 'chromedriver is not available on this host';
    private const CHROME_SKIP_MESSAGE = 'Chrome binary is not available on this host';
    private const CHROMEDRIVER_BINARY = 'drivers/chromedriver';
    private const CHROME_BINARY_CANDIDATES = [
        'google-chrome',
        'google-chrome-stable',
        'chromium',
        'chromium-browser',
        'chrome',
    ];
    private const PROCESS_TIMEOUT_SECONDS = 30;
    private const SERVER_HEALTH_ATTEMPTS = 50;
    private const SERVER_HEALTH_INTERVAL_MICROSECONDS = 200_000;
    private const CHROMEDRIVER_CHECK_TIMEOUT_SECONDS = 5;
    private const EXCEPTION_HANDLER_RESTORE_ATTEMPTS = 32;
    private static int $serverPort;
    private static ?Process $serverProcess = null;

    public static function setUpBeforeClass(): void
    {
        self::configurePantherEnvironment();
        self::$serverPort = self::allocatePort();

        self::startLocalServer();
    }

    public static function tearDownAfterClass(): void
    {
        self::stopLocalServer();
    }

    public function testCanonicalTransactionRouteIsReachableInChromium(): void
    {
        self::requireChromeDriver();
        self::requireChromeBinary();
        $client = $this->createLocalDevClient();

        try {
            $client->request('GET', self::CANONICAL_TRANSACTION_CREATE_PATH);

            self::assertSame(self::METHOD_NOT_ALLOWED_STATUS, $client->getInternalResponse()->getStatusCode());
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

    private static function startLocalServer(): void
    {
        if ('Windows' !== PHP_OS_FAMILY) {
            self::runServerCommand(self::SERVER_START_SCRIPT);

            return;
        }

        self::$serverProcess = new Process([
            PHP_BINARY,
            '-S',
            sprintf('%s:%d', self::SERVER_HOST, self::$serverPort),
            '-t',
            'public',
            'public/index.php',
        ], self::projectRoot(), self::serverCommandEnvironment());
        self::$serverProcess->setTimeout(null);
        self::$serverProcess->start();

        self::waitForServerReady();
    }

    private static function stopLocalServer(): void
    {
        if ('Windows' !== PHP_OS_FAMILY) {
            self::runServerCommand(self::SERVER_STOP_SCRIPT);

            return;
        }

        if (self::$serverProcess instanceof Process && self::$serverProcess->isRunning()) {
            self::$serverProcess->stop(1.0);
        }

        self::$serverProcess = null;
    }

    private static function runServerCommand(string $relativeScriptPath): void
    {
        $process = new Process([self::projectPath($relativeScriptPath)], self::projectRoot(), self::serverCommandEnvironment());
        $process->setTimeout(self::PROCESS_TIMEOUT_SECONDS);
        $process->mustRun();
    }

    private static function waitForServerReady(): void
    {
        for ($attempt = 0; $attempt < self::SERVER_HEALTH_ATTEMPTS; ++$attempt) {
            if (self::$serverProcess instanceof Process && !self::$serverProcess->isRunning()) {
                throw new \RuntimeException(sprintf('Local PHP server stopped before accepting connections: %s', self::$serverProcess->getErrorOutput()));
            }

            $socket = @fsockopen(self::SERVER_HOST, self::$serverPort, $errorCode, $errorMessage, 0.2);
            if (is_resource($socket)) {
                fclose($socket);

                return;
            }

            usleep(self::SERVER_HEALTH_INTERVAL_MICROSECONDS);
        }

        throw new \RuntimeException(sprintf('Local PHP server failed to accept connections at %s.', self::serverBaseUri()));
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

        $nameEntity = stream_socket_get_name($socket, false);
        fclose($socket);

        if (!is_string($nameEntity) || !preg_match('/:(\d+)$/', $nameEntity, $matches)) {
            throw new \RuntimeException('Failed to resolve allocated port.');
        }

        return (int) $matches[1];
    }

    private static function restoreExceptionHandlerStack(): void
    {
        for ($attempt = 0; $attempt < self::EXCEPTION_HANDLER_RESTORE_ATTEMPTS; ++$attempt) {
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

    private static function setPantherEnv(string $nameEntity, string $value): void
    {
        putenv(sprintf('%s=%s', $nameEntity, $value));
        $_ENV[$nameEntity] = $value;
        $_SERVER[$nameEntity] = $value;
    }

    private static function prependPath(string $directory): void
    {
        $path = getenv('PATH');
        $normalizedPath = is_string($path) && '' !== $path ? $directory.PATH_SEPARATOR.$path : $directory;

        self::setPantherEnv('PATH', $normalizedPath);
    }

    private static function requireChromeDriver(): void
    {
        $process = new Process([self::chromeDriverBinaryPath(), '--version'], self::projectRoot());
        $process->setTimeout(self::CHROMEDRIVER_CHECK_TIMEOUT_SECONDS);
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        self::markTestSkipped(self::CHROMEDRIVER_SKIP_MESSAGE);
    }

    private static function quitClient(Client $client): void
    {
        try {
            $client->quit();
        } catch (\Throwable) {
            // Panther may already have torn down chromedriver on shutdown.
        }
    }

    private static function requireChromeBinary(): void
    {
        foreach (self::CHROME_BINARY_CANDIDATES as $candidate) {
            $process = new Process([$candidate, '--version'], self::projectRoot());
            $process->setTimeout(self::CHROMEDRIVER_CHECK_TIMEOUT_SECONDS);
            $process->run();

            if ($process->isSuccessful()) {
                return;
            }
        }

        self::markTestSkipped(self::CHROME_SKIP_MESSAGE);
    }

    private static function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * @return array{HOST:string, PORT:string}
     */
    private static function serverCommandEnvironment(): array
    {
        return [
            'HOST' => self::SERVER_HOST,
            'PORT' => (string) self::$serverPort,
        ];
    }

    private static function projectPath(string $relativePath): string
    {
        return self::projectRoot().'/'.ltrim($relativePath, '/');
    }

    private static function chromeDriverBinaryPath(): string
    {
        return self::projectPath(self::CHROMEDRIVER_BINARY);
    }
}
