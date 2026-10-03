<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Category;

use App\Vendoring\Service\Category\VendorCategoryCollectionService;
use PHPUnit\Framework\TestCase;

final class VendorCategoryCollectionServiceTest extends TestCase
{
    public function testAndRuleRequiresEveryPredicate(): void
    {
        $service = new VendorCategoryCollectionService();
        $products = [
            ['id' => 1, 'tags' => ['featured'], 'categoryIds' => ['10'], 'price' => 120.0],
            ['id' => 2, 'tags' => ['featured'], 'categoryIds' => ['10'], 'price' => 80.0],
            ['id' => 3, 'tags' => ['standard'], 'categoryIds' => ['10'], 'price' => 140.0],
        ];

        $result = $service->filter($products, 'tag:featured AND category:10 AND price>=100');

        self::assertSame([$products[0]], $result);
    }

    public function testOrRuleAcceptsAnyPredicate(): void
    {
        $service = new VendorCategoryCollectionService();
        $products = [
            ['id' => 1, 'tags' => ['standard'], 'categoryIds' => ['20'], 'price' => 20.0],
            ['id' => 2, 'tags' => ['featured'], 'categoryIds' => ['20'], 'price' => 20.0],
            ['id' => 3, 'tags' => ['standard'], 'categoryIds' => ['10'], 'price' => 150.0],
        ];

        $result = $service->filter($products, 'tag:featured OR category:10 OR price>200');

        self::assertSame([$products[1], $products[2]], $result);
    }

    public function testInvalidTokensPreserveEmptyPredicateSemantics(): void
    {
        $service = new VendorCategoryCollectionService();
        $products = [
            ['id' => 1],
            ['id' => 2],
        ];

        self::assertSame($products, $service->filter($products, 'not-a-rule'));
        self::assertSame([], $service->filter($products, 'OR not-a-rule'));
    }

    public function testScalarTagsAndCategoryIdsAreNormalizedToStrings(): void
    {
        $service = new VendorCategoryCollectionService();
        $products = [
            ['id' => 1, 'tags' => [42, true], 'categoryIds' => [7]],
            ['id' => 2, 'tags' => [['nested']], 'categoryIds' => [8]],
        ];

        self::assertSame([$products[0]], $service->filter($products, 'tag:42 AND category:7'));
    }
}
