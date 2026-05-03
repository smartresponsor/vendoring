<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class InterfaceAliasCoverageTest extends TestCase
{
    public function testServicesConfigurationCoversCanonicalRepositoryAndServiceInterfaces(): void
    {
        $config = (string) file_get_contents(dirname(__DIR__, 3) . '/config/component/services.yaml');
        $aliasMap = $this->extractAliasMap($config);

        self::assertNotSame([], $aliasMap, 'No interface aliases were found in config/component/services.yaml.');

        foreach ($aliasMap as $interfaceClass => $implementationClass) {
            self::assertTrue(
                interface_exists($interfaceClass),
                'Missing canonical interface ' . $interfaceClass,
            );
            self::assertTrue(
                class_exists($implementationClass),
                'Missing implementation class ' . $implementationClass,
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function extractAliasMap(string $config): array
    {
        preg_match_all(
            "/^\\s{2}(App\\\\Vendoring\\\\(?:RepositoryInterface|ServiceInterface|PolicyInterface)\\\\[^:\\s]+):\\s*'@([^']+)'/m",
            $config,
            $matches,
            PREG_SET_ORDER,
        );

        $aliases = [];
        foreach ($matches as $match) {
            $aliases[$match[1]] = $match[2];
        }

        return $aliases;
    }
}
