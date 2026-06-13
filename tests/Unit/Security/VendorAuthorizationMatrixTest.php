<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Service\Security\VendorAuthorizationMatrixService;
use App\Vendoring\ValueObject\VendorRoleValueObject;
use PHPUnit\Framework\TestCase;

final class VendorAuthorizationMatrixTest extends TestCase
{
    public function testOwnerHasFullVendorLocalAccessSet(): void
    {
        $matrix = new VendorAuthorizationMatrixService();

        self::assertTrue($matrix->can(VendorRoleValueObject::OWNER, 'transactions.write'));
        self::assertTrue($matrix->can(VendorRoleValueObject::OWNER, 'payouts.write'));
        self::assertTrue($matrix->can(VendorRoleValueObject::OWNER, 'statements.send'));
        self::assertTrue($matrix->can(VendorRoleValueObject::OWNER, 'ownership.write'));
    }

    public function testViewerRemainsReadOnly(): void
    {
        $matrix = new VendorAuthorizationMatrixService();

        self::assertTrue($matrix->can(VendorRoleValueObject::VIEWER, 'transactions.read'));
        self::assertFalse($matrix->can(VendorRoleValueObject::VIEWER, 'transactions.write'));
        self::assertFalse($matrix->can(VendorRoleValueObject::VIEWER, 'payouts.write'));
    }

    public function testUnknownCapabilityFailsClosed(): void
    {
        $matrix = new VendorAuthorizationMatrixService();

        self::assertFalse($matrix->can(VendorRoleValueObject::OWNER, 'unknown.capability'));
        self::assertSame([], $matrix->capabilitiesForRole('superadmin'));
    }
}
