<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$matrixPath = $root . '/delivery/audit/vendoring-wave14-entity-to-template-coverage.json';

if (!is_file($matrixPath)) {
    fwrite(STDERR, "Missing coverage matrix: {$matrixPath}\n");
    exit(1);
}

$data = json_decode((string) file_get_contents($matrixPath), true, 512, JSON_THROW_ON_ERROR);
$summary = $data['summary'] ?? [];
$matrix = $data['matrix'] ?? [];

$controllerFiles = glob($root . '/src/Controller/**/*.php') ?: [];
if ($controllerFiles !== []) {
    fwrite(STDERR, "Vendoring must remain zero-controller; controller files detected.\n");
    foreach ($controllerFiles as $file) {
        fwrite(STDERR, "  - " . substr($file, strlen($root) + 1) . "\n");
    }
    exit(1);
}

$serviceNamingViolations = [];
foreach (glob($root . '/src/Service/Http/Vendor/**/*.php') ?: [] as $file) {
    $class = basename($file, '.php');
    if ($class === 'AbstractVendorCrudRouteService') {
        continue;
    }
    if (!str_starts_with($class, 'Vendor') || !str_ends_with($class, 'Service')) {
        $serviceNamingViolations[] = substr($file, strlen($root) + 1);
    }
}

$formNamingViolations = [];
foreach (glob($root . '/src/Form/Vendor/**/*.php') ?: [] as $file) {
    $class = basename($file, '.php');
    if (!str_starts_with($class, 'Vendor') || !str_ends_with($class, 'Type')) {
        $formNamingViolations[] = substr($file, strlen($root) + 1);
    }
}

if ($serviceNamingViolations !== [] || $formNamingViolations !== []) {
    fwrite(STDERR, "Vendor zero-controller naming violations detected.\n");
    foreach ($serviceNamingViolations as $file) {
        fwrite(STDERR, "  service: {$file}\n");
    }
    foreach ($formNamingViolations as $file) {
        fwrite(STDERR, "  form: {$file}\n");
    }
    exit(1);
}

$hiddenCandidates = [];
foreach ($matrix as $row) {
    if (($row['entity'] ?? '') === 'VendorAbstractEntity') {
        continue;
    }
    if (($row['routeKeys'] ?? []) === []) {
        $hiddenCandidates[] = $row['entity'];
    }
}

echo "Vendoring Wave 14 entity-to-template coverage audit OK\n";
echo "Entity graph nodes: " . ($summary['entityCount'] ?? count($matrix)) . "\n";
echo "Route entries: " . ($summary['routeEntryCount'] ?? 0) . "\n";
echo "Entities with routes: " . ($summary['entitiesWithRoutes'] ?? 0) . "\n";
echo "Entities without routes/policy: " . count($hiddenCandidates) . "\n";
echo "Direct entity templates: " . ($summary['entitiesWithDirectTemplate'] ?? 0) . "\n";
echo "Controller files: 0\n";

if ($hiddenCandidates !== []) {
    echo "\nCoverage gaps are documented in docs/internal/VENDORING_ENTITY_TO_TEMPLATE_COVERAGE_AUDIT.md\n";
}
