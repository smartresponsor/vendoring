<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use PHPUnit\Framework\TestCase;

final class ComposerConfigurationSmokeTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $composer;

    protected function setUp(): void
    {
        $composerFile = dirname(__DIR__, 2).'/composer.json';
        $decoded = json_decode((string) file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        $this->composer = $decoded;
    }

    public function testRuntimePhpConstraintIsCanonical(): void
    {
        self::assertSame('^8.4', $this->composer['require']['php'] ?? null);
    }

    public function testQualityScriptsAreRegistered(): void
    {
        $scripts = $this->composer['scripts'] ?? [];

        self::assertArrayHasKey('lint:php', $scripts);
        self::assertArrayHasKey('test:smoke', $scripts);
        self::assertArrayHasKey('test:unit', $scripts);
        self::assertArrayHasKey('phpstan', $scripts);
        self::assertArrayHasKey('test', $scripts);
        self::assertArrayHasKey('quality', $scripts);
        self::assertArrayHasKey('test:mail', $scripts);
        self::assertArrayHasKey('test:compat', $scripts);
    }

    public function testDevToolsAreDeclared(): void
    {
        $requireDev = $this->composer['require-dev'] ?? [];

        self::assertArrayHasKey('friendsofphp/php-cs-fixer', $requireDev);
        self::assertArrayHasKey('phpstan/phpstan', $requireDev);
        self::assertArrayHasKey('phpunit/phpunit', $requireDev);
    }

    public function testRuntimeSymfonyPackagesAreDeclared(): void
    {
        $require = $this->composer['require'] ?? [];

        self::assertArrayHasKey('symfony/console', $require);
        self::assertArrayHasKey('symfony/framework-bundle', $require);
        self::assertArrayHasKey('symfony/http-foundation', $require);
        self::assertArrayHasKey('symfony/mailer', $require);
        self::assertArrayHasKey('symfony/mime', $require);
        self::assertArrayHasKey('symfony/routing', $require);
        self::assertArrayHasKey('symfony/uid', $require);
    }

    public function testRuntimeDoctrinePackagesAreDeclared(): void
    {
        $require = $this->composer['require'] ?? [];

        self::assertArrayHasKey('doctrine/dbal', $require);
        self::assertArrayHasKey('doctrine/doctrine-bundle', $require);
        self::assertArrayHasKey('doctrine/orm', $require);
    }
}
