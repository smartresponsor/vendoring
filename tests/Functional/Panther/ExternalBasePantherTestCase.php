<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Functional\Panther;

use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Process\Process;

abstract class ExternalBasePantherTestCase extends PantherTestCase
{
    private static ?Process $ownedServer = null;
    private static ?KernelInterface $ownedKernel = null;
    private static string $ownedBaseUri = '';
    private static string $token = '';
    protected static string $transactionVendorId = '';
    private ?Client $isolatedClient = null;

    public static function setUpBeforeClass(): void
    {
        if ('1' !== getenv('PANTHER_ISOLATED_ACCEPTANCE')) {
            return;
        }
        $root = dirname(__DIR__, 3);
        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase($root);
        self::$ownedKernel = $kernel;
        self::$token = KernelRuntimeHarness::seedFinancialApiKey($kernel, 'tenant-1', '42', 'read:finance,write:transactions,read:transactions', $vendorId);
        self::$transactionVendorId = (string) $vendorId;
        $dsn = $_ENV['VENDOR_DSN'] ?? null;
        if (!is_string($dsn) || !str_starts_with($dsn, 'sqlite:///')) {
            throw new \RuntimeException('Isolated SQLite database required.');
        }
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if (false === $socket) {
            throw new \RuntimeException('Cannot allocate isolated HTTP port.');
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        if (!is_string($address)) {
            throw new \RuntimeException('Cannot resolve isolated HTTP port.');
        }
        self::$ownedBaseUri = 'http://'.$address;
        self::$ownedServer = new Process(
            [PHP_BINARY, '-S', $address, '-t', 'public', 'public/index.php'],
            $root,
            [
                'APP_ENV' => $kernel->getEnvironment().'_browser',
                'APP_DEBUG' => '0',
                'APP_SECRET' => bin2hex(random_bytes(32)),
                'VENDOR_DSN' => $dsn,
                'VENDOR_RUNTIME_HARNESS' => '0',
            ],
        );
        self::$ownedServer->disableOutput();
        self::$ownedServer->setTimeout(null);
        self::$ownedServer->start();
        for ($attempt = 0; $attempt < 100; ++$attempt) {
            if (!self::$ownedServer->isRunning()) {
                throw new \RuntimeException('Isolated HTTP server stopped.');
            }
            $context = stream_context_create(['http' => ['timeout' => 1]]);
            $body = @file_get_contents(self::$ownedBaseUri.'/healthz', false, $context);
            if (is_string($body) && str_contains($body, '"status":"ok"')) {
                return;
            }
            usleep(200000);
        }
        throw new \RuntimeException('Isolated HTTP server not ready.');
    }

    public static function tearDownAfterClass(): void
    {
        self::$ownedServer?->stop(1.0);
        self::$ownedServer = null;
        if (null !== self::$ownedKernel) {
            KernelRuntimeHarness::cleanupRuntimeState(self::$ownedKernel);
        }
        self::$ownedKernel = null;
        self::$token = '';
        self::$ownedBaseUri = '';
    }

    protected function createExternalBaseClient(): Client
    {
        if (null === self::$ownedServer) {
            self::markTestSkipped('Set PANTHER_ISOLATED_ACCEPTANCE=1 to run isolated Vendoring browser acceptance.');
        }
        $driverDir = getenv('PANTHER_TEST_DRIVER_DIR');
        if (is_string($driverDir) && '' !== $driverDir) {
            $path = $driverDir.PATH_SEPARATOR.(getenv('PATH') ?: '');
            putenv('PATH='.$path);
            $_SERVER['PATH'] = $_ENV['PATH'] = $path;
        }
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        if (false === $socket) {
            throw new \RuntimeException('Cannot allocate ChromeDriver port.');
        }
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        if (!is_string($address)) {
            throw new \RuntimeException('Cannot resolve ChromeDriver port.');
        }
        $port = (int) substr(strrchr($address, ':') ?: ':0', 1);
        $client = Client::createChromeClient(null, null, ['port' => $port], self::$ownedBaseUri);
        $this->isolatedClient = $client;
        $client->request('GET', '/healthz');

        return $client;
    }

    protected function tearDown(): void
    {
        if (null !== $this->isolatedClient) {
            $this->isolatedClient->quit();
            $this->isolatedClient = null;
        }
        parent::tearDown();
    }

    /** @param array<string, mixed>|null $payload
     * @return array{status:int, body:string}
     */
    protected static function browserRequest(Client $client, string $method, string $path, ?array $payload = null, bool $authenticated = true, bool $form = false): array
    {
        $result = $client->executeAsyncScript(
            'const done = arguments[arguments.length - 1]; const [url, method, token, payload, form] = arguments; const headers = {}; if (token) headers.Authorization = "Bearer " + token; const options = {method, headers, signal: AbortSignal.timeout(15000)}; if (payload !== null) {headers["Content-Type"] = form ? "application/x-www-form-urlencoded" : "application/json"; options.body = form ? new URLSearchParams(payload) : JSON.stringify(payload);} fetch(url, options).then(async r => done({status: r.status, body: await r.text()})).catch(() => done({status: 0, body: ""}));',
            [$path, $method, $authenticated ? self::$token : '', $payload, $form],
        );
        self::assertIsArray($result);
        self::assertIsInt($result['status'] ?? null);
        self::assertIsString($result['body'] ?? null);

        return ['status' => $result['status'], 'body' => $result['body']];
    }

    /** @param array{status:int,body:string} $response
     * @return array<string,mixed>
     */
    protected static function decodeJsonResponse(array $response, int $expectedStatusCode = 200): array
    {
        self::assertSame($expectedStatusCode, $response['status']);
        $payload = json_decode($response['body'], true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        $object = [];
        foreach ($payload as $key => $value) {
            self::assertIsString($key);
            $object[$key] = $value;
        }

        return $object;
    }
}
