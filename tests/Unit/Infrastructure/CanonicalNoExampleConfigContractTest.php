<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoExampleConfigContractTest extends TestCase
{
    public function testRepositoryDoesNotContainActiveExampleDomainMarkersInOperationalConfig(): void
    {
        $files = [
            __DIR__ . '/../../../ops/policy/config/crm.yaml',
            __DIR__ . '/../../../ops/policy/config/shadow.yaml',
            __DIR__ . '/../../../ops/policy/config/api_v1_cors.yaml',
            __DIR__ . '/../../../ops/policy/config/services_interface.yaml',
        ];

        foreach ($files as $file) {
            self::assertFileExists($file);
            $content = (string) file_get_contents($file);
            self::assertStringNotContainsString('example.com', $content, $file);
            self::assertStringNotContainsString('service example', strtolower($content), $file);
        }
    }
}
