<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Security\VendorTransactionMachineRequestMatcher;
use App\Vendoring\Service\Transaction\VendorTransactionHttpService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionMachineRequestMatcherTest extends TestCase
{
    #[DataProvider('routes')]
    public function testOnlyExactProtectedEndpointsSelectMachineFirewall(string $method, string $path, string $controller, bool $expected): void
    {
        $request = Request::create($path, $method);
        $request->attributes->set('_controller', $controller);
        self::assertSame($expected, (new VendorTransactionMachineRequestMatcher())->matches($request));
    }

    /** @return iterable<array{string, string, string, bool}> */
    public static function routes(): iterable
    {
        $service = VendorTransactionHttpService::class.'::';
        yield ['POST', '/api/vendor/transaction', $service.'create', true];
        yield ['GET', '/api/vendor/transaction/42', $service.'listByVendor', true];
        yield ['GET', '/api/vendor/transaction/list/42', $service.'listByVendor', true];
        yield ['POST', '/api/vendor/transaction/status/42/3', $service.'updateStatus', true];
        yield ['POST', '/api/vendor/transaction/status/3', $service.'updateStatusById', true];
        yield ['GET', '/api/vendor/transaction/list/42', 'App\Service\HumanService::show', false];
        yield ['GET', '/api/vendor/transaction/list/0042', $service.'listByVendor', false];
        yield ['GET', '/api/vendor/transaction/list/vendor-42', $service.'listByVendor', false];
        yield ['POST', '/api/vendor/transaction/list/42', $service.'listByVendor', false];
        yield ['GET', '/api/vendor/profile/show/42', $service.'listByVendor', false];
        yield ['GET', '/ops/vendor/transaction/42', $service.'listByVendor', false];
        yield ['GET', '/api/access/me', $service.'listByVendor', false];
    }
}
