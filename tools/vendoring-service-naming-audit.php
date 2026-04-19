<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__);
$serviceDir = $root . '/src/Service';

if (!is_dir($serviceDir)) {
    fwrite(STDERR, "Service directory not found: {$serviceDir}\n");
    exit(1);
}

/** @var array<string, true> $componentScopedNamespaces */
$componentScopedNamespaces = [
    'App\Vendoring\\Service\\Payout\\' => true,
    'App\Vendoring\\Service\\Ledger\\' => true,
    'App\Vendoring\\Service\\Statement\\' => true,
    'App\Vendoring\\Service\\WebhooksConsumer\\' => true,
    'App\Vendoring\\Service\\' => true,
];

/** @var array<string, true> $alwaysAllowed */
$alwaysAllowed = [
    'App\Vendoring\\Service\\VendorService' => true,
    'App\Vendoring\\Service\\VendorPassportService' => true,
    'App\Vendoring\\Service\\VendorUserAssignmentService' => true,
    'App\Vendoring\\Service\\VendorProfileService' => true,
    'App\Vendoring\\Service\\VendorDocumentService' => true,
    'App\Vendoring\\Service\\VendorMediaService' => true,
    'App\Vendoring\\Service\\VendorBillingService' => true,
    'App\Vendoring\\Service\\VendorApiKeyService' => true,
    'App\Vendoring\\Service\\VendorSecurityService' => true,
    'App\Vendoring\\Service\\Statement\\VendorStatementService' => true,
    'App\Vendoring\\Service\\Metric\\VendorMetricService' => true,
    'App\Vendoring\\Service\\Ledger\\VendorSummaryService' => true,
];

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($serviceDir));
$violations = [];

foreach ($rii as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = (string) file_get_contents($path);

    if (!preg_match('/^namespace\\s+([^;]+);/m', $content, $nsMatch)) {
        continue;
    }

    if (!preg_match('/^final\\s+class\\s+([A-Za-z0-9_]+)/m', $content, $classMatch)) {
        continue;
    }

    $fqn = trim($nsMatch[1]) . '\\' . trim($classMatch[1]);
    $shortName = trim($classMatch[1]);

    if (!str_ends_with($shortName, 'Service')) {
        continue;
    }

    if (isset($alwaysAllowed[$fqn])) {
        continue;
    }

    $isScoped = false;
    foreach ($componentScopedNamespaces as $namespacePrefix => $_) {
        if (str_starts_with($fqn, $namespacePrefix)) {
            $isScoped = true;
            break;
        }
    }

    if (!$isScoped) {
        continue;
    }

    if (str_starts_with($shortName, 'Vendor')) {
        continue;
    }

    $suggestedShortName = 'Vendor' . $shortName;
    $relativePath = str_replace($root . '/', '', $path);

    $violations[] = [
        'class' => $fqn,
        'file' => $relativePath,
        'suggested_class' => trim($nsMatch[1]) . '\\' . $suggestedShortName,
        'suggested_file' => str_replace($shortName . '.php', $suggestedShortName . '.php', $relativePath),
    ];
}

usort(
    $violations,
    static fn(array $a, array $b): int => strcmp($a['class'], $b['class']),
);

$result = [
    'generated_at_utc' => gmdate(DATE_ATOM),
    'rule' => 'Component-scoped services should use Vendor*Service naming. Reusable services may keep neutral names.',
    'violations' => $violations,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
