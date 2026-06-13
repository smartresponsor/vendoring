<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;

final class VendorFixtureStackContractTest extends TestCase
{
    public function testTransactionDemoFixtureUsesDoctrineFixturesWithoutDql(): void
    {
        $fixturePath = dirname(__DIR__, 3).'/src/DataFixtures/VendorTransactionDemoFixture.php';
        $source = file_get_contents($fixturePath);

        self::assertIsString($source);
        self::assertStringContainsString('extends Fixture', $source);
        self::assertStringNotContainsString('createQuery(', $source);
        self::assertStringNotContainsString('createQueryBuilder(', $source);
        self::assertStringNotContainsString('DQL', $source);
    }

    public function testOwnershipWriteFixtureIsNotPartOfCurrentRuntimeSlice(): void
    {
        $fixturePath = dirname(__DIR__, 3).'/src/DataFixtures/VendorOwnershipDemoFixture.php';

        self::assertFileDoesNotExist($fixturePath);
    }
}
