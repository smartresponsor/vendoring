<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$objects = ['document', 'media'];
$actions = ['index', 'show', 'new', 'create', 'edit', 'update', 'delete', 'bulk', 'import', 'export', 'archive', 'restore', 'duplicate'];
$typed = ['create', 'update', 'delete', 'bulk', 'import', 'export', 'archive', 'restore', 'duplicate'];

foreach ($objects as $object) {
    $Object = ucfirst($object);
    $routeMap = $root.'/config/platform/routes/crud/vendor.attachment.'.$object.'.yaml';
    if (!file_exists($routeMap)) {
        $errors[] = 'Missing route map: '.$routeMap;
    }

    foreach ($actions as $action) {
        $Action = ucfirst($action);
        $service = $root.'/src/Service/Http/Vendor/Attachment/'.$Object.'/VendorAttachment'.$Object.$Action.'Service.php';
        if (!file_exists($service)) {
            $errors[] = 'Missing service: '.$service;
        }

        if (in_array($action, $typed, true)) {
            $type = $root.'/src/Form/Vendor/Attachment/'.$Object.'/VendorAttachment'.$Object.$Action.'Type.php';
            if (!file_exists($type)) {
                $errors[] = 'Missing form type: '.$type;
            }
        }
    }
}

if ([] !== $errors) {
    fwrite(STDERR, 'Vendor attachment route-map smoke failed:
');
    foreach ($errors as $error) {
        fwrite(STDERR, ' - '.$error.'
');
    }
    exit(1);
}

echo 'Vendor attachment route-map smoke passed.
';
