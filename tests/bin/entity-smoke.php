<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$requiredClasses = [
    App\Entity\Vendor::class,
    App\Entity\VendorApiKey::class,
    App\Entity\VendorAnalytics::class,
    App\Entity\VendorAttachment::class,
    App\Entity\VendorBilling::class,
    App\Entity\VendorDocument::class,
    App\Entity\VendorLedgerBinding::class,
    App\Entity\VendorMedia::class,
    App\Entity\VendorPassport::class,
    App\Entity\VendorProfile::class,
    App\Entity\VendorSecurity::class,
    App\Entity\VendorTransaction::class,
];

foreach ($requiredClasses as $className) {
    if (!class_exists($className)) {
        fwrite(STDERR, '[FAIL] missing class ' . $className . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $className . PHP_EOL);
}
