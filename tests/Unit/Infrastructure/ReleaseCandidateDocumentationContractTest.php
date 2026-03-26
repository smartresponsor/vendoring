<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/bin/_composer_json.php';

final class ReleaseCandidateDocumentationContractTest extends TestCase
{
    public function testReadmeReferencesReleaseCandidateLanesAndDocs(): void
    {
        $readme = (string) file_get_contents(dirname(__DIR__, 3).'/README.md');

        self::assertStringContainsString('composer quality:release-candidate', $readme);
        self::assertStringContainsString('docs/release/RC_ROADMAP.md', $readme);
        self::assertStringContainsString('headless/backend component', $readme);
    }

    public function testComposerDefinesGroupedReleaseCandidateQualityLanes(): void
    {
        $composer = vendoring_load_composer_json(dirname(__DIR__, 3));
        $scripts = vendoring_composer_scripts($composer);

        foreach (['quality:static', 'quality:contracts', 'quality:runtime', 'quality:persistence', 'quality:api', 'quality:docs', 'quality:release-candidate', 'test:release-candidate-docs'] as $script) {
            self::assertArrayHasKey($script, $scripts, 'Missing grouped RC script: '.$script);
            self::assertIsArray($scripts[$script], 'Grouped RC script must be an array: '.$script);
            self::assertNotSame([], $scripts[$script], 'Grouped RC script must not be empty: '.$script);
        }
    }

    public function testPhpunitDefinesVendoringSuiteForDomainFocusedSlices(): void
    {
        $phpunit = (string) file_get_contents(dirname(__DIR__, 3).'/phpunit.xml.dist');

        self::assertStringContainsString('<testsuite name="unit">', $phpunit);
        self::assertStringContainsString('<testsuite name="integration">', $phpunit);
        self::assertStringContainsString('<testsuite name="smoke">', $phpunit);
    }

    public function testReleaseCandidateWorkflowsExistAndReferenceGroupedScripts(): void
    {
        $root = dirname(__DIR__, 3);
        $quality = (string) file_get_contents($root.'/.github/workflows/quality.yml');
        $runtime = (string) file_get_contents($root.'/.github/workflows/runtime.yml');
        $docs = (string) file_get_contents($root.'/.github/workflows/docs.yml');
        $aggregate = (string) file_get_contents($root.'/.github/workflows/release-candidate.yml');

        self::assertStringContainsString('composer quality:static', $quality);
        self::assertStringContainsString('composer quality:contracts', $quality);
        self::assertStringContainsString('composer quality:runtime', $runtime);
        self::assertStringContainsString('composer quality:persistence', $runtime);
        self::assertStringContainsString('composer quality:api', $runtime);
        self::assertStringContainsString('composer quality:docs', $docs);
        self::assertStringContainsString('quality:release-candidate', $aggregate);
    }
}
