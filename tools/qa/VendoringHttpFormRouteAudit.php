<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$matrixFile = $root . '/delivery/audit/vendoring-wave12b-http-form-decision-matrix.json';
if (!is_file($matrixFile)) {
    fwrite(STDERR, "Missing decision matrix: {$matrixFile}\n");
    exit(1);
}
$matrix = json_decode((string) file_get_contents($matrixFile), true, 512, JSON_THROW_ON_ERROR);
$missing = [];
foreach ($matrix['routes'] ?? [] as $route) {
    foreach (['service', 'form_type'] as $field) {
        $fqcn = $route[$field] ?? null;
        if (!is_string($fqcn) || $fqcn === '') {
            continue;
        }
        if (!str_starts_with($fqcn, 'App\\')) {
            $missing[] = [$route['route_key'] ?? '?', $field, $fqcn, 'non-App namespace'];
            continue;
        }
        $relative = str_replace('\\', '/', substr($fqcn, 4)) . '.php';
        $file = $root . '/src/' . $relative;
        if (!is_file($file)) {
            $missing[] = [$route['route_key'] ?? '?', $field, $fqcn, $file];
        }
    }
}
if ($missing !== []) {
    foreach ($missing as [$routeKey, $field, $fqcn, $detail]) {
        fwrite(STDERR, "Missing {$field} target for {$routeKey}: {$fqcn} ({$detail})\n");
    }
    exit(1);
}
$summary = $matrix['summary'] ?? [];
echo "Vendoring Wave 12B route surface audit OK\n";
echo "Routes: " . ($summary['route_entries'] ?? 0) . "\n";
echo "HTTP services: " . ($summary['unique_http_services'] ?? 0) . "\n";
echo "Form types: " . ($summary['unique_form_types'] ?? 0) . "\n";
foreach (($summary['decision_counts'] ?? []) as $decision => $count) {
    echo $decision . ': ' . $count . "\n";
}
