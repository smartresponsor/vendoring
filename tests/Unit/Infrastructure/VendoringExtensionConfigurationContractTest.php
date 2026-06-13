<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use App\Vendoring\DependencyInjection\VendorConfiguration;
use App\Vendoring\DependencyInjection\VendoringExtension;
use PHPUnit\Framework\TestCase;

final class VendoringExtensionConfigurationContractTest extends TestCase
{
    public function testExtensionPublishesVendoringAlias(): void
    {
        $extension = new VendoringExtension();

        self::assertSame('vendoring', $extension->getAlias());
    }

    public function testConfigurationRootIsVendoring(): void
    {
        $configuration = new VendorConfiguration();
        $tree = $configuration->getConfigTreeBuilder();

        self::assertSame('vendoring', $tree->buildTree()->getName());
    }
}
