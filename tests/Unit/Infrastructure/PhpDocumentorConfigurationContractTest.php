<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class PhpDocumentorConfigurationContractTest extends TestCase
{
    public function testPhpDocumentorConfigurationAndGeneratorArePresent(): void
    {
        self::assertFileExists(__DIR__.'/../../../phpdoc.dist.xml');
        self::assertFileExists(__DIR__.'/../../../bin/generate-phpdocumentor-site.php');
    }

    public function testPhpDocumentorConfigurationTargetsBuildDocsOutput(): void
    {
        $contents = (string) file_get_contents(__DIR__.'/../../../phpdoc.dist.xml');

        self::assertStringContainsString('<output>build/docs/phpdocumentor/api</output>', $contents);
        self::assertStringContainsString('<path>src</path>', $contents);
        self::assertStringContainsString('<path>tests</path>', $contents);
    }
}
