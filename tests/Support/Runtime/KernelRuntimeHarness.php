<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Support\Runtime;

use App\Entity\Vendor;
use App\Entity\VendorApiKey;
use App\Entity\VendorTransaction;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
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

        $databaseDsn = 'sqlite:///' . $databaseFile;

        $_ENV['APP_ENV'] = $environment;
        $_SERVER['APP_ENV'] = $environment;
        $_ENV['APP_DEBUG'] = $debug ? '1' : '0';
        $_SERVER['APP_DEBUG'] = $debug ? '1' : '0';
        $_ENV['APP_SECRET'] = 'vendoring-test-secret';
        $_SERVER['APP_SECRET'] = 'vendoring-test-secret';
        $_ENV['VENDOR_DSN'] = $databaseDsn;
        $_SERVER['VENDOR_DSN'] = $databaseDsn;
        $_ENV['VENDOR_RUNTIME_HARNESS'] = '1';
        $_SERVER['VENDOR_RUNTIME_HARNESS'] = '1';

        chdir($projectRoot);

        $kernel = new Kernel($environment, $debug);
        $kernel->boot();
        $cacheDir = $kernel->getCacheDir();

        $container = $kernel->getContainer();
        $doctrine = $container->get('doctrine');

        if (!$doctrine instanceof ManagerRegistry) {
            throw new \RuntimeException('Doctrine manager registry is not available in runtime harness.');
        }

        $entityManager = $doctrine->getManager();

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Runtime harness expected an EntityManagerInterface instance.');
        }
        self::createRuntimeSchema($entityManager);

        register_shutdown_function(static function () use ($databaseFile, $entityManager, $kernel, $cacheDir): void {
            try {
                if ($entityManager->isOpen()) {
                    $entityManager->clear();
                }

                $connection = $entityManager->getConnection();

                if ($connection->isConnected()) {
                    $connection->close();
                }
            } catch (\Throwable) {
                // best-effort runtime harness cleanup
            }

            $kernel->shutdown();
            gc_collect_cycles();
            self::removeDirectory($cacheDir);

            if (!is_file($databaseFile)) {
                return;
            }

            for ($attempt = 0; $attempt < 5; ++$attempt) {
                if (@unlink($databaseFile) || !is_file($databaseFile)) {
                    return;
                }

                usleep(100000);
                clearstatcache(true, $databaseFile);
            }

            fwrite(STDERR, sprintf("Failed to remove sqlite database file: %s\n", $databaseFile));
        });

        return $kernel;
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<string, string>     $headers
     */
    public static function requestJson(KernelInterface $kernel, string $method, string $uri, ?array $payload = null, array $headers = []): JsonResponse
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        foreach ($headers as $name => $value) {
            $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            $server[$normalized] = $value;
        }
        $content = null === $payload ? null : json_encode($payload, JSON_THROW_ON_ERROR);
        $request = Request::create($uri, $method, server: $server, content: $content);
        $response = $kernel->handle($request);

        if ($kernel instanceof Kernel) {
            $kernel->terminate($request, $response);
        }

        if (!$response instanceof JsonResponse) {
            throw new \RuntimeException('Expected JsonResponse from kernel runtime harness.');
        }

        return $response;
    }

    /** @param array<string, scalar|array<string, scalar>|null> $payload */
    public static function requestForm(KernelInterface $kernel, string $method, string $uri, array $payload = []): Response
    {
        $request = Request::create($uri, $method, $payload);
        $response = $kernel->handle($request);

        if ($kernel instanceof Kernel) {
            $kernel->terminate($request, $response);
        }

        return $response;
    }

    public static function cleanupRuntimeState(?KernelInterface $kernel = null): void
    {
        if (null !== $kernel) {
            $kernel->shutdown();
            self::removeDirectory($kernel->getCacheDir());
        }

        self::restoreExceptionHandlerStack();
        gc_collect_cycles();
    }

    public static function assertRedirectTo(RedirectResponse|Response $response, string $expectedLocation): void
    {
        if (!$response instanceof RedirectResponse && !$response->isRedirect()) {
            throw new \RuntimeException('Expected redirect response from kernel runtime harness.');
        }

        if ($response->headers->get('Location') !== $expectedLocation) {
            throw new \RuntimeException('Unexpected redirect target: ' . (string) $response->headers->get('Location'));
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

    public static function seedActiveApiKey(KernelInterface $kernel, string $permissions = 'write:transactions'): string
    {
        $container = $kernel->getContainer();
        $doctrine = $container->get('doctrine');

        if (!$doctrine instanceof ManagerRegistry) {
            throw new \RuntimeException('Doctrine manager registry is not available in runtime harness.');
        }

        $entityManager = $doctrine->getManager();
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \RuntimeException('Runtime harness expected an EntityManagerInterface instance.');
        }

        $vendor = new Vendor('Runtime Harness Vendor ' . bin2hex(random_bytes(4)));
        $vendor->activate();
        $entityManager->persist($vendor);
        $entityManager->flush();

        $plainToken = bin2hex(random_bytes(16));
        $apiKey = new VendorApiKey($vendor, hash('sha256', $plainToken), $permissions);
        $entityManager->persist($apiKey);
        $entityManager->flush();

        return $plainToken;
    }

    private static function createRuntimeSchema(EntityManagerInterface $entityManager): void
    {
        $schemaTool = new SchemaTool($entityManager);
        $metadata = [
            $entityManager->getClassMetadata(Vendor::class),
            $entityManager->getClassMetadata(VendorApiKey::class),
            $entityManager->getClassMetadata(VendorTransaction::class),
        ];
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement(sprintf(
            'CREATE TABLE payouts (
                id VARCHAR(64) PRIMARY KEY,
                vendor_id VARCHAR(255) NOT NULL,
                currency VARCHAR(16) NOT NULL,
                gross_cents INTEGER NOT NULL,
                fee_cents INTEGER NOT NULL,
                net_cents INTEGER NOT NULL,
                status VARCHAR(32) NOT NULL,
                created_at VARCHAR(32) NOT NULL,
                processed_at VARCHAR(32) DEFAULT NULL,
                meta %s NOT NULL
            )',
            $platform->getClobTypeDeclarationSQL([]),
        ));

        $connection->executeStatement('CREATE TABLE payout_items (
            id VARCHAR(64) PRIMARY KEY,
            payout_id VARCHAR(64) NOT NULL,
            entry_id VARCHAR(64) NOT NULL,
            amount_cents INTEGER NOT NULL
        )');

        $connection->executeStatement('CREATE TABLE vendor_ledger_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id VARCHAR(255) NOT NULL,
            vendor_id VARCHAR(255) DEFAULT NULL,
            reference_type VARCHAR(255) NOT NULL,
            reference_id VARCHAR(255) NOT NULL,
            debit_account VARCHAR(255) NOT NULL,
            credit_account VARCHAR(255) NOT NULL,
            amount NUMERIC NOT NULL,
            currency VARCHAR(16) NOT NULL,
            created_at VARCHAR(32) NOT NULL
        )');
    }

    private static function removeDirectory(string $path): void
    {
        if ('' === $path || !is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }

            if ($item->isDir()) {
                @rmdir($item->getPathname());

                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($path);
    }
}
