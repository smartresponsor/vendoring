<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class OpenApiDocumentationContractTest extends TestCase
{
    public function testOpenApiGeneratorAndNelmioScaffoldArePresent(): void
    {
        self::assertFileExists(__DIR__ . '/../../../bin/generate-openapi.php');
        self::assertFileExists(__DIR__ . '/../../../config/packages/vendor_nelmio_api_doc.yaml.dist');
        self::assertFileExists(__DIR__ . '/../../../config/routes/vendor_nelmio_api_doc.yaml.dist');
    }

    public function testVendorTransactionControllerContainsDocblockContractMarkers(): void
    {
        $contents = (string) file_get_contents(__DIR__ . '/../../../src/Controller/Vendor/VendorTransactionController.php');

        self::assertStringContainsString('Create a vendor transaction from a JSON payload.', $contents);
        self::assertStringContainsString('List all transactions for a single vendor.', $contents);
        self::assertStringContainsString('Update the status of a vendor transaction.', $contents);
    }
}
