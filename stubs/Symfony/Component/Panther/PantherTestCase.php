<?php

declare(strict_types=1);

namespace Symfony\Component\Panther;

use PHPUnit\Framework\TestCase;

/**
 * Static-analysis fallback for environments where symfony/panther is not installed.
 * Runtime browser execution should use the real Panther package.
 */
abstract class PantherTestCase extends TestCase
{
    public const string CHROME = 'chrome';

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $kernelOptions
     * @param array<string, mixed> $managerOptions
     */
    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): Client
    {
        return new Client();
    }

    protected static function assertPageTitleContains(string $expected): void
    {
    }

    protected static function assertSelectorTextContains(string $selector, string $expected): void
    {
    }
}
