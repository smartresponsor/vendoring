<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeFile = $root.'/config/platform/routes/crud/vendor_crud.yaml';
$source = file_get_contents($routeFile) ?: '';
$errors = [];

$requiredRoutes = [
    'vendor.index',
    'vendor.show_id',
    'vendor.new',
    'vendor.create',
    'vendor.edit_id',
    'vendor.update_id',
    'vendor.delete_id',
];

$forbiddenRoutes = [
    'vendor.show_slug',
    'vendor.edit_slug',
    'vendor.update_slug',
    'vendor.delete_slug',
    'vendor.bulk',
    'vendor.import',
    'vendor.export',
    'vendor.archive_id',
    'vendor.archive_slug',
    'vendor.restore_id',
    'vendor.restore_slug',
    'vendor.duplicate_id',
    'vendor.duplicate_slug',
];

foreach ($requiredRoutes as $route) {
    if (!preg_match('/^'.preg_quote($route, '/').':/m', $source)) {
        $errors[] = 'Missing required route: '.$route;
    }
}

foreach ($forbiddenRoutes as $route) {
    if (preg_match('/^'.preg_quote($route, '/').':/m', $source)) {
        $errors[] = 'Unsupported route remains active: '.$route;
    }
}

foreach ([
    'App\\Vendoring\\Service\\VendorIndexService',
    'App\\Vendoring\\Service\\VendorShowService',
    'App\\Vendoring\\Service\\VendorNewService',
    'App\\Vendoring\\Service\\VendorCreateService',
    'App\\Vendoring\\Service\\VendorEditService',
    'App\\Vendoring\\Service\\VendorUpdateService',
    'App\\Vendoring\\Service\\VendorDeleteService',
    'App\\Vendoring\\Form\\VendorCreateType',
    'App\\Vendoring\\Form\\VendorUpdateType',
] as $fqcn) {
    $relative = preg_replace('/^App\\\\Vendoring\\\\/', 'src/', $fqcn);
    $path = $root.'/'.str_replace('\\', '/', (string) $relative).'.php';

    if (!is_file($path)) {
        $errors[] = 'Missing active target: '.$fqcn;
    }
}

$entity = file_get_contents($root.'/src/Entity/Vendor/VendorEntity.php') ?: '';
if (preg_match('/\\$slug\\b|function\\s+getSlug\\s*\\(/', $entity)) {
    $errors[] = 'VendorEntity now has slug support; route policy must be reviewed.';
}

$response = file_get_contents($root.'/src/Service/VendorHttpRouteResponseService.php') ?: '';
foreach ([
    'vendor/index.html.twig',
    'vendor/show.html.twig',
    'vendor/form.html.twig',
] as $candidate) {
    if (!str_contains($response, $candidate)) {
        $errors[] = 'Missing template candidate: '.$candidate;
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "Vendoring core entrypoint responsibility audit OK\n");
fwrite(STDOUT, "Active routes: 6\n");
fwrite(STDOUT, "Active form types: 2\n");
fwrite(STDOUT, "Slug routes: disabled (Objecting slug exists; repository lookup and route services are not implemented)\n");
fwrite(STDOUT, "Unsupported generic CRUD routes: not registered\n");
