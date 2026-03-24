<?php

declare(strict_types=1);

namespace App\Tests\Support\Runtime;

use App\Entity\Vendor\VendorTransaction;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class KernelRuntimeHarness
{
    public static function createKernelWithFreshSqliteDatabase(string $projectRoot, string $environment = 'test', bool $debug = true): KernelInterface
    {
        if (!extension_loaded('pdo_sqlite')) {
            throw new \RuntimeException('pdo_sqlite extension is required.');
        }

        $databaseFile = tempnam(sys_get_temp_dir(), 'vendoring_runtime_');

        if (false === $databaseFile) {
            throw new \RuntimeException('Failed to allocate sqlite database file.');
        }

        $databaseDsn = 'sqlite:///'.$databaseFile;

        $_ENV['APP_ENV'] = $environment;
        $_SERVER['APP_ENV'] = $environment;
        $_ENV['APP_DEBUG'] = $debug ? '1' : '0';
        $_SERVER['APP_DEBUG'] = $debug ? '1' : '0';
        $_ENV['APP_SECRET'] = 'vendoring-test-secret';
        $_SERVER['APP_SECRET'] = 'vendoring-test-secret';
        $_ENV['VENDOR_DSN'] = $databaseDsn;
        $_SERVER['VENDOR_DSN'] = $databaseDsn;

        chdir($projectRoot);

        $kernel = new Kernel($environment, $debug);
        $kernel->boot();

        $container = $kernel->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        self::createVendorTransactionSchema($entityManager);

        register_shutdown_function(static function () use ($databaseFile): void {
            if (is_file($databaseFile)) {
                @unlink($databaseFile);
            }
        });

        return $kernel;
    }

    public static function requestJson(KernelInterface $kernel, string $method, string $uri, ?array $payload = null): JsonResponse
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        $content = null === $payload ? null : json_encode($payload, JSON_THROW_ON_ERROR);
        $request = Request::create($uri, $method, server: $server, content: $content);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        if (!$response instanceof JsonResponse) {
            throw new \RuntimeException('Expected JsonResponse from kernel runtime harness.');
        }

        return $response;
    }

    public static function requestForm(KernelInterface $kernel, string $method, string $uri, array $payload = []): Response
    {
        $request = Request::create($uri, $method, $payload);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        return $response;
    }

    public static function assertRedirectTo(RedirectResponse|Response $response, string $expectedLocation): void
    {
        if (!$response instanceof RedirectResponse && !($response instanceof Response && $response->isRedirect())) {
            throw new \RuntimeException('Expected redirect response from kernel runtime harness.');
        }

        if ($response->headers->get('Location') !== $expectedLocation) {
            throw new \RuntimeException('Unexpected redirect target: '.(string) $response->headers->get('Location'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function decodeJson(JsonResponse $response): array
    {
        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $payload;
    }

    private static function createVendorTransactionSchema(EntityManagerInterface $entityManager): void
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadata = [$entityManager->getClassMetadata(VendorTransaction::class)];
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }
}
