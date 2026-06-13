<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeFile = $root.'/config/platform/routes/crud/vendor.yaml';
$source = file_get_contents($routeFile) ?: '';
$errors = [];

$requiredRoutes = [
    'vendor.index',
    'vendor.show_id',
    'vendor.new',
    'vendor.create',
    'vendor.edit_id',
    'vendor.update_id',
];

$forbiddenRoutes = [
    'vendor.show_slug',
    'vendor.edit_slug',
    'vendor.update_slug',
    'vendor.delete_id',
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
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorIndexService',
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorShowService',
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorNewService',
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorCreateService',
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorEditService',
    'App\\Vendoring\\Service\\Http\\Vendor\\VendorUpdateService',
    'App\\Vendoring\\Form\\Vendor\\VendorCreateType',
    'App\\Vendoring\\Form\\Vendor\\VendorUpdateType',
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

$response = file_get_contents($root.'/src/Service/Http/Vendor/VendorHttpRouteResponseService.php') ?: '';
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
fwrite(STDOUT, "Slug routes: disabled (VendorEntity has no slug)\n");
fwrite(STDOUT, "Unsupported generic CRUD routes: not registered\n");
