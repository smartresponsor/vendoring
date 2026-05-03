<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoExampleCommandHelpContractTest extends TestCase
{
    public function testVendorApiKeyCommandsDoNotUseExampleHelpWording(): void
    {
        $files = [
            __DIR__ . '/../../../src/Command/VendorApiKeyCreateCommand.php',
            __DIR__ . '/../../../src/Command/VendorApiKeyListCommand.php',
            __DIR__ . '/../../../src/Command/VendorApiKeyRotateCommand.php',
        ];

        foreach ($files as $file) {
            self::assertFileExists($file);
            $content = (string) file_get_contents($file);
            self::assertStringNotContainsString("->setHelp('Example:", $content, $file);
        }
    }
}
