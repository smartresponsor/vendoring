<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Service\Category\Rule\VendorCategoryRuleEngineService;
use PHPUnit\Framework\TestCase;

final class VendorCategoryRuleEngineHistoricalContractTest extends TestCase
{
    public function testNullGroupDoesNotOverrideComparisonOrMatchEverything(): void
    {
        $service = new VendorCategoryRuleEngineService();
        self::assertFalse($service->match(['condition' => ['all' => null]], []));
        self::assertFalse($service->match(['condition' => ['none' => null]], []));
        self::assertTrue($service->match(['condition' => ['all' => null, 'attr' => 'state', 'op' => 'eq', 'value' => 'active']], ['state' => 'active']));
        self::assertFalse($service->match(['condition' => ['all' => null, 'attr' => 'state', 'op' => 'eq', 'value' => 'active']], ['state' => 'inactive']));
        self::assertTrue($service->match(['condition' => ['all' => []]], []));
        self::assertFalse($service->match(['condition' => ['any' => []]], []));
    }
}
