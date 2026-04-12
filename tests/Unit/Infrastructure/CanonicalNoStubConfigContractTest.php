<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class CanonicalNoStubConfigContractTest extends TestCase
{
    public function testOpsPolicyConfigsDoNotUseStubProviders(): void
    {
        $pairs = [
            dirname(__DIR__, 3) . '/ops/policy/config/crm.yaml' => 'provider: "stub"',
            dirname(__DIR__, 3) . '/ops/policy/config/kms.yaml' => "provider: 'stub'",
        ];

        foreach ($pairs as $file => $forbidden) {
            self::assertFileExists($file);
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString($forbidden, $contents, $file);
        }
    }
}
