<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root.'/vendor/autoload.php';

$requiredClasses = [
    App\Entity\Vendor\Vendor::class,
    App\Entity\Vendor\VendorApiKey::class,
    App\Entity\Vendor\VendorAnalytics::class,
    App\Entity\Vendor\VendorAttachment::class,
    App\Entity\Vendor\VendorBilling::class,
    App\Entity\Vendor\VendorDocument::class,
    App\Entity\Vendor\VendorLedgerBinding::class,
    App\Entity\Vendor\VendorMedia::class,
    App\Entity\Vendor\VendorPassport::class,
    App\Entity\Vendor\VendorProfile::class,
    App\Entity\Vendor\VendorSecurity::class,
    App\Entity\Vendor\VendorTransaction::class,
];

foreach ($requiredClasses as $className) {
    if (!class_exists($className)) {
        fwrite(STDERR, '[FAIL] missing class '.$className.PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] '.$className.PHP_EOL);
}
