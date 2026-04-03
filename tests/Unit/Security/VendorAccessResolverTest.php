<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\VendorUserAssignment;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Service\Security\VendorAccessResolver;
use App\Service\Security\VendorAuthorizationMatrix;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorAccessResolverTest extends TestCase
{
    private VendorUserAssignmentRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
    }

    public function testCapabilityGrantedWhenAnyActiveRoleMatches(): void
    {
        $this->repository
            ->method('findActiveByVendorId')
            ->with(42)
            ->willReturn([
                new VendorUserAssignment(42, 7, 'viewer'),
                new VendorUserAssignment(42, 7, 'finance'),
            ]);

        $resolver = new VendorAccessResolver($this->repository, new VendorAuthorizationMatrix());

        self::assertTrue($resolver->canUserAccessVendorCapability(42, 7, 'payouts.write'));
        self::assertSame('role_grants_capability', $resolver->explainUserAccessVendorCapability(42, 7, 'payouts.write')['reason']);
    }

    public function testCapabilityDeniedWithoutAssignments(): void
    {
        $this->repository->method('findActiveByVendorId')->with(42)->willReturn([]);

        $resolver = new VendorAccessResolver($this->repository, new VendorAuthorizationMatrix());
        $explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'ownership.read');

        self::assertFalse($explanation['granted']);
        self::assertSame('no_active_assignment', $explanation['reason']);
        self::assertSame([], $explanation['roles']);
    }

    public function testCapabilityDeniedWhenRolesDoNotGrantPermission(): void
    {
        $this->repository
            ->method('findActiveByVendorId')
            ->with(42)
            ->willReturn([
                new VendorUserAssignment(42, 7, 'viewer'),
            ]);

        $resolver = new VendorAccessResolver($this->repository, new VendorAuthorizationMatrix());
        $explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'ownership.write');

        self::assertFalse($explanation['granted']);
        self::assertSame('capability_not_granted', $explanation['reason']);
        self::assertSame(['viewer'], $explanation['roles']);
    }
}
