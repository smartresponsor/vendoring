<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Service\Security\VendorAuthorizationMatrix;
use App\Vendoring\ValueObject\VendorRole;
use PHPUnit\Framework\TestCase;

final class VendorAuthorizationMatrixTest extends TestCase
{
    public function testOwnerHasFullVendorLocalAccessSet(): void
    {
        $matrix = new VendorAuthorizationMatrix();

        self::assertTrue($matrix->can(VendorRole::OWNER, 'transactions.write'));
        self::assertTrue($matrix->can(VendorRole::OWNER, 'payouts.write'));
        self::assertTrue($matrix->can(VendorRole::OWNER, 'statements.send'));
        self::assertTrue($matrix->can(VendorRole::OWNER, 'ownership.write'));
    }

    public function testViewerRemainsReadOnly(): void
    {
        $matrix = new VendorAuthorizationMatrix();

        self::assertTrue($matrix->can(VendorRole::VIEWER, 'transactions.read'));
        self::assertFalse($matrix->can(VendorRole::VIEWER, 'transactions.write'));
        self::assertFalse($matrix->can(VendorRole::VIEWER, 'payouts.write'));
    }

    public function testUnknownCapabilityFailsClosed(): void
    {
        $matrix = new VendorAuthorizationMatrix();

        self::assertFalse($matrix->can(VendorRole::OWNER, 'unknown.capability'));
        self::assertSame([], $matrix->capabilitiesForRole('superadmin'));
    }
}
