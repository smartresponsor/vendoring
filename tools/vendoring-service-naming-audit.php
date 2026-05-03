<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

/**
 * Vendoring service naming audit.
 *
 * Canon:
 * - concrete services live under App\Vendoring\Service\...
 * - concrete service short names use Vendor*Service
 * - retired legacy aliases are rejected explicitly
 * - VendorEntity*Service is not required; VendorEntity is reserved for PHP/Doctrine entity terminology
 */

$root = dirname(__DIR__);
$serviceDir = $root . '/src/Service';

if (!is_dir($serviceDir)) {
    fwrite(STDERR, "Service directory not found: {$serviceDir}\n");
    exit(1);
}

/** @var array<string, string> $retiredServiceNames */
$retiredServiceNames = [
    'VendorService' => 'VendorCoreService',
    'VendorSecurityService' => 'VendorAccessResolverService/VendorAuthorizationMatrixService/VendorSecurityStateProjectionBuilderService',
    'VendorTransactionManagerService' => 'VendorTransactionLifecycleService',
    'VendorStatementExporterPDFService' => 'VendorStatementExporterPdfService',
    'VendorTfidfSearchService' => 'VendorTfIdfSearchService',
    'VendorFileOutboundCircuitBreakerService' => 'VendorOutboundCircuitBreakerService',
    'VendorFileWriteRateLimiterService' => 'VendorWriteRateLimiterService',
    'VendorFileObservabilityRecordExporterService' => 'VendorObservabilityRecordExporterService',
    'VendorChainMetricCollectorService' => 'VendorMetricCollectorService',
];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serviceDir, FilesystemIterator::SKIP_DOTS));
$violations = [];

foreach ($rii as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
        continue;
    }

    $path = $file->getPathname();
    $content = (string) file_get_contents($path);

    if (!preg_match('/^namespace\s+([^;]+);/m', $content, $nsMatch)) {
        $violations[] = [
            'type' => 'namespace',
            'file' => str_replace($root . '/', '', $path),
            'message' => 'missing namespace',
        ];
        continue;
    }

    if (!preg_match('/^(?:final\s+)?class\s+([A-Za-z0-9_]+)/m', $content, $classMatch)) {
        continue;
    }

    $namespace = trim($nsMatch[1]);
    $shortName = trim($classMatch[1]);
    $fqn = $namespace . '\\' . $shortName;
    $relativePath = str_replace($root . '/', '', $path);

    if (!str_starts_with($fqn, 'App\\Vendoring\\Service\\')) {
        $violations[] = [
            'type' => 'namespace',
            'class' => $fqn,
            'file' => $relativePath,
            'message' => 'service class is outside App\\Vendoring\\Service',
        ];
        continue;
    }

    if (isset($retiredServiceNames[$shortName])) {
        $violations[] = [
            'type' => 'retired_name',
            'class' => $fqn,
            'file' => $relativePath,
            'message' => 'retired service name is still present',
            'replacement' => $retiredServiceNames[$shortName],
        ];
        continue;
    }

    if (!str_starts_with($shortName, 'Vendor')) {
        $violations[] = [
            'type' => 'prefix',
            'class' => $fqn,
            'file' => $relativePath,
            'message' => 'service short name must start with Vendor',
        ];
    }

    if (!str_ends_with($shortName, 'Service')) {
        $violations[] = [
            'type' => 'suffix',
            'class' => $fqn,
            'file' => $relativePath,
            'message' => 'service short name must end with Service',
        ];
    }
}

usort(
    $violations,
    static fn(array $a, array $b): int => strcmp((string) ($a['file'] ?? ''), (string) ($b['file'] ?? '')),
);

$result = [
    'generated_at_utc' => gmdate(DATE_ATOM),
    'rule' => 'Concrete services must use App\\Vendoring\\Service\\... and Vendor*Service names; retired aliases are forbidden.',
    'violations' => $violations,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit([] === $violations ? 0 : 1);
