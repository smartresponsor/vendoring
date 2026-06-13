<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$requiredClasses = [
    App\Vendoring\Entity\Vendor\VendorEntity::class,
    App\Vendoring\Entity\Vendor\VendorApiKeyEntity::class,
    App\Vendoring\Entity\Vendor\VendorAnalyticsEntity::class,
    App\Vendoring\Entity\Vendor\VendorAttachmentEntity::class,
    App\Vendoring\Entity\Vendor\VendorBillingEntity::class,
    App\Vendoring\Entity\Vendor\VendorDocumentEntity::class,
    App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity::class,
    App\Vendoring\Entity\Vendor\VendorMediaEntity::class,
    App\Vendoring\Entity\Vendor\VendorPassportEntity::class,
    App\Vendoring\Entity\Vendor\VendorProfileEntity::class,
    App\Vendoring\Entity\Vendor\VendorSecurityEntity::class,
    App\Vendoring\Entity\Vendor\VendorTransactionEntity::class,
];

foreach ($requiredClasses as $className) {
    if (!class_exists($className)) {
        fwrite(STDERR, '[FAIL] missing class ' . $className . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $className . PHP_EOL);
}
