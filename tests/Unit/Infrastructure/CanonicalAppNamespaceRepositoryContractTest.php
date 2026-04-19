<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalAppNamespaceRepositoryContractTest extends TestCase
{
    public function testRepositoryOperationalServiceConfigUsesCanonicalAppNamespace(): void
    {
        $file = __DIR__ . '/../../../ops/policy/config/services_interface.yaml';

        self::assertFileExists($file);

        $content = (string) file_get_contents($file);

        self::assertStringContainsString('App\Vendoring\\:', $content);
        self::assertStringContainsString('App\Vendoring\\ServiceInterface\\Order\\OrderPaymentInterface:', $content);
        self::assertStringContainsString('alias: App\Vendoring\\Service\\Order\\OrderPaymentService', $content);
        self::assertStringNotContainsString('Vendor\\:', $content);
        self::assertStringNotContainsString('Vendor\\ServiceInterface\\Order\\OrderPaymentInterface:', $content);
        self::assertStringNotContainsString('alias: Vendor\\Service\\Order\\OrderPaymentService', $content);
    }
}
