<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$servicesPath = $root.'/config/component/services.yaml';
$services = file_get_contents($servicesPath);

if (false === $services) {
    fwrite(STDERR, "Cannot read services.yaml\n");
    exit(1);
}

$errors = [];
$repositoryCount = 0;
foreach (glob($root.'/src/Repository/Vendor/*Repository.php') ?: [] as $repositoryFile) {
    $repository = basename($repositoryFile, '.php');
    $interface = 'App\\Vendoring\\RepositoryInterface\\Vendor\\'.$repository.'Interface';
    $implementation = 'App\\Vendoring\\Repository\\Vendor\\'.$repository;
    $expected = $interface.": '@".$implementation."'";
    if (!str_contains($services, $expected)) {
        $errors[] = 'Missing repository binding: '.$expected;
    }
    ++$repositoryCount;
}

$obsoleteMarkers = [
    'Wave 11: persistence-bound services below import absent',
    "- '../../src/Service/Payout/VendorPayoutService.php'",
    "- '../../src/Service/Core/VendorCoreService.php'",
    "- '../../src/Service/Transaction/VendorTransactionLifecycleService.php'",
];
foreach ($obsoleteMarkers as $marker) {
    if (str_contains($services, $marker)) {
        $errors[] = 'Obsolete quarantine marker remains: '.$marker;
    }
}

$requiredMarkers = [
    "App\\Vendoring\\Service\\Http\\:\n    resource: '../../src/Service/Http/'",
    "App\\Vendoring\\Form\\:\n    resource: '../../src/Form/'",
    "App\\Vendoring\\ServiceInterface\\Profile\\VendorProfileAttachmentResolverServiceInterface: '@App\\Vendoring\\Service\\Profile\\NullVendorProfileAttachmentResolverService'",
    "App\\Vendoring\\Service\\Config\\VendoringFeatureFlagsConfigService:",
    '$projectDir: \'%kernel.project_dir%\'',
];
foreach ($requiredMarkers as $marker) {
    if (!str_contains($services, $marker)) {
        $errors[] = 'Required registration marker missing: '.$marker;
    }
}

if ([] !== $errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error."\n");
    }
    exit(1);
}

echo "Vendoring Wave 16 service registration reconciliation audit OK\n";
echo "Repository bindings: {$repositoryCount}\n";
echo "HTTP/Form resources: active\n";
echo "Persistence-bound business services: active\n";
echo "Optional Attaching bridge: isolated\n";
