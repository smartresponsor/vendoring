<?php

declare(strict_types=1);

namespace App\Tests\Integration\Security;

use App\Service\VendorApiKeyService;
use App\Service\VendorSecurityService;
use PHPUnit\Framework\TestCase;

final class VendorApiKeySecurityConsistencyTest extends TestCase
{
    public function testInvalidPermissionDoesNotResolveVendor(): void
    {
        $apiKeys = new VendorApiKeyService();
        $security = new VendorSecurityService($apiKeys);

        $token = $apiKeys->issueToken('tenant-1', '101', ['read_only']);

        $resolved = $security->resolveVendorByToken($token, 'write_access');

        self::assertNull($resolved);
    }

    public function testValidPermissionResolvesVendor(): void
    {
        $apiKeys = new VendorApiKeyService();
        $security = new VendorSecurityService($apiKeys);

        $token = $apiKeys->issueToken('tenant-1', '101', ['write_access']);

        $resolved = $security->resolveVendorByToken($token, 'write_access');

        self::assertSame('101', $resolved);
    }
}
