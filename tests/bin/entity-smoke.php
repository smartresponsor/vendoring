<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$requiredClasses = [
    App\Vendoring\Entity\Vendor::class,
    App\Vendoring\Entity\VendorApiKey::class,
    App\Vendoring\Entity\VendorAnalytics::class,
    App\Vendoring\Entity\VendorAttachment::class,
    App\Vendoring\Entity\VendorBilling::class,
    App\Vendoring\Entity\VendorDocument::class,
    App\Vendoring\Entity\VendorLedgerBinding::class,
    App\Vendoring\Entity\VendorMedia::class,
    App\Vendoring\Entity\VendorPassport::class,
    App\Vendoring\Entity\VendorProfile::class,
    App\Vendoring\Entity\VendorSecurity::class,
    App\Vendoring\Entity\VendorTransaction::class,
];

foreach ($requiredClasses as $className) {
    if (!class_exists($className)) {
        fwrite(STDERR, '[FAIL] missing class ' . $className . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $className . PHP_EOL);
}
