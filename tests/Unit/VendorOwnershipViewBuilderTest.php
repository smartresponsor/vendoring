<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Vendor;
use App\Entity\VendorUserAssignment;
use App\RepositoryInterface\VendorRepositoryInterface;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Service\Security\VendorAuthorizationMatrix;
use App\Service\VendorOwnershipViewBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorOwnershipViewBuilderTest extends TestCase
{
    private VendorRepositoryInterface&MockObject $vendorRepository;
    private VendorUserAssignmentRepositoryInterface&MockObject $assignmentRepository;

    protected function setUp(): void
    {
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->assignmentRepository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
    }

    public function testBuildForVendorIdIncludesCapabilitiesPerAssignment(): void
    {
        $vendor = new Vendor(brandName: 'Acme', ownerUserId: 7);

        $this->vendorRepository->method('find')->with(42)->willReturn($vendor);
        $this->assignmentRepository->method('findActiveByVendorId')->with(42)->willReturn([
            new VendorUserAssignment(42, 7, 'owner', isPrimary: true),
            new VendorUserAssignment(42, 9, 'viewer'),
        ]);

        $builder = new VendorOwnershipViewBuilder(
            $this->vendorRepository,
            $this->assignmentRepository,
            new VendorAuthorizationMatrix(),
        );

        $payload = $builder->buildForVendorId(42)?->toArray();

        self::assertNotNull($payload);
        self::assertSame(['transactions.read', 'transactions.write', 'payouts.read', 'payouts.write', 'statements.read', 'statements.send', 'ownership.read', 'ownership.write'], $payload['assignments'][0]['capabilities']);
        self::assertSame(['transactions.read', 'payouts.read', 'statements.read', 'ownership.read'], $payload['assignments'][1]['capabilities']);
    }
}
