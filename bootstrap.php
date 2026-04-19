<?php

declare(strict_types=1);

$autoload = __DIR__ . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Vendoring\\Tests\\' => __DIR__ . '/tests/',
        'App\\Vendoring\\' => __DIR__ . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        if ($relative === false || $relative === '') {
            return;
        }

        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});
