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
    'App\\Service\\Payout\\' => true,
    'App\\Service\\Ledger\\' => true,
    'App\\Service\\Statement\\' => true,
    'App\\Service\\WebhooksConsumer\\' => true,
    'App\\Service\\' => true,
];

/** @var array<string, true> $alwaysAllowed */
$alwaysAllowed = [
    'App\\Service\\VendorService' => true,
    'App\\Service\\VendorPassportService' => true,
    'App\\Service\\VendorUserAssignmentService' => true,
    'App\\Service\\VendorProfileService' => true,
    'App\\Service\\VendorDocumentService' => true,
    'App\\Service\\VendorMediaService' => true,
    'App\\Service\\VendorBillingService' => true,
    'App\\Service\\VendorApiKeyService' => true,
    'App\\Service\\VendorSecurityService' => true,
    'App\\Service\\Statement\\VendorStatementService' => true,
    'App\\Service\\Metric\\VendorMetricService' => true,
    'App\\Service\\Ledger\\VendorSummaryService' => true,
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
