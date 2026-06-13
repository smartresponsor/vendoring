<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$services = [
    'src/Service/Http/Vendor/VendorHttpRouteResponseService.php',
    'src/Service/Http/Vendor/VendorIndexService.php',
    'src/Service/Http/Vendor/VendorShowService.php',
    'src/Service/Http/Vendor/Attachment/Document/VendorAttachmentDocumentIndexService.php',
    'src/Service/Http/Vendor/Attachment/Document/VendorAttachmentDocumentShowService.php',
    'src/Service/Http/Vendor/Attachment/Media/VendorAttachmentMediaIndexService.php',
    'src/Service/Http/Vendor/Attachment/Media/VendorAttachmentMediaShowService.php',
];

foreach ($services as $service) {
    $path = $root . '/' . $service;
    if (!is_file($path)) {
        fwrite(STDERR, "Missing service: {$service}\n");
        exit(1);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fwrite(STDERR, "Unreadable service: {$service}\n");
        exit(1);
    }

    if (str_contains($source, "'status' => 'skeleton'")) {
        fwrite(STDERR, "Skeleton status still present in implemented read service: {$service}\n");
        exit(1);
    }

    if (str_contains($source, 'App\\Vendoring\\Entity\\') || str_contains($source, 'App\\Vendoring\\Repository')) {
        fwrite(STDERR, "Persistence dependency found in read service: {$service}\n");
        exit(1);
    }
}

$responseService = file_get_contents($root . '/src/Service/Http/Vendor/VendorHttpRouteResponseService.php');
if (!str_contains((string) $responseService, 'read_route_ready')) {
    fwrite(STDERR, "Read route response service does not emit read_route_ready status.\n");
    exit(1);
}

fwrite(STDOUT, "Vendoring Wave 12C HTTP read route implementation audit OK\n");
