<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$requiredClasses = [
    App\Vendoring\Entity\Vendor\VendorEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorApiKeyEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorAnalyticsEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorAttachmentEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorBillingEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorDocumentEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorLedgerBindingEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorMediaEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorPassportEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorProfileEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorSecurityEntity::class,
    App\Vendoring\Entity\Vendor\VendorEntity\VendorTransactionEntity::class,
];

foreach ($requiredClasses as $className) {
    if (!class_exists($className)) {
        fwrite(STDERR, '[FAIL] missing class ' . $className . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, '[OK] ' . $className . PHP_EOL);
}
