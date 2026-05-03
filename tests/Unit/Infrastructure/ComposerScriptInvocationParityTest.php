<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

require_once dirname(__DIR__, 2) . '/bin/_composer_json.php';

use PHPUnit\Framework\TestCase;

final class ComposerScriptInvocationParityTest extends TestCase
{
    public function testPhpunitInvocationsUsePhpPrefixAndQualityHasNoDuplicates(): void
    {
        $composer = vendoring_load_composer_json(dirname(__DIR__, 3));
        $scripts = vendoring_composer_section($composer, 'scripts');

        foreach ($scripts as $name => $commands) {
            if (!is_array($commands)) {
                continue;
            }

            foreach ($commands as $command) {
                if (!is_string($command)) {
                    continue;
                }

                self::assertStringNotContainsString(
                    'vendor/bin/phpunit ',
                    preg_replace('/^php\s+vendor\/bin\/phpunit\s+/', '', $command) ?? $command,
                    'Non-canonical phpunit invocation remains in script: ' . $name,
                );

                if (str_contains($command, 'vendor/bin/phpunit')) {
                    self::assertStringStartsWith(
                        'php vendor/bin/phpunit ',
                        $command,
                        'Phpunit command must be prefixed with php in script: ' . $name,
                    );
                }
            }
        }

        $quality = $scripts['quality'] ?? [];
        self::assertIsArray($quality);
        $quality = array_values(array_filter($quality, 'is_string'));
        self::assertSame($quality, array_values(array_unique($quality)), 'Quality script contains duplicate entries.');
    }
}
