<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class ReleaseCandidateRuntimeActivationContractTest extends TestCase
{
    public function testComposerDeclaresRuntimeActivationPackages(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../../../composer.json'), true, flags: JSON_THROW_ON_ERROR);
        $require = $composer['require'] ?? [];

        self::assertArrayHasKey('twig/twig', $require);
        self::assertArrayHasKey('symfony/twig-bundle', $require);
        self::assertArrayHasKey('symfony/form', $require);
        self::assertArrayHasKey('symfony/validator', $require);
        self::assertArrayHasKey('symfony/security-csrf', $require);
        self::assertArrayHasKey('nelmio/api-doc-bundle', $require);
    }

    public function testRuntimeActivationConfigurationAndTemplateArePresent(): void
    {
        self::assertFileExists(__DIR__.'/../../../config/packages_runtime.php');
        self::assertFileExists(__DIR__.'/../../../config/routes_runtime.php');
        self::assertFileExists(__DIR__.'/../../../config/services_runtime.php');
        self::assertFileExists(__DIR__.'/../../../config/routes/nelmio_api_doc.yaml');
        self::assertFileExists(__DIR__.'/../../../templates/ops/vendor_transactions/index.html.twig');
    }

    public function testFormDefinitionsArePresent(): void
    {
        self::assertFileExists(__DIR__.'/../../../src/Form/Ops/VendorTransactionCreateInput.php');
        self::assertFileExists(__DIR__.'/../../../src/Form/Ops/VendorTransactionCreateType.php');
        self::assertFileExists(__DIR__.'/../../../src/Form/Ops/VendorTransactionStatusUpdateInput.php');
        self::assertFileExists(__DIR__.'/../../../src/Form/Ops/VendorTransactionStatusUpdateType.php');
    }

    public function testKernelLoadsRuntimeActivationFiles(): void
    {
        $contents = (string) file_get_contents(__DIR__.'/../../../src/Kernel.php');

        self::assertStringContainsString('packages_runtime.php', $contents);
        self::assertStringContainsString('services_runtime.php', $contents);
        self::assertStringContainsString('routes_runtime.php', $contents);
    }
}
