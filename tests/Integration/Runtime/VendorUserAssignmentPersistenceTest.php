<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\Repository\VendorRepository;
use App\Vendoring\Repository\VendorUserAssignmentRepository;
use App\Vendoring\Service\Assignment\VendorUserAssignmentService;
use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class VendorUserAssignmentPersistenceTest extends TestCase
{
    public function testAssignmentPersistsExistingVendorAndRejectsUnknownVendor(): void
    {
        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        try {
            $vendor = KernelRuntimeHarness::seedActiveVendor($kernel);
            $id = $vendor->getId();
            self::assertNotNull($id);
            $registry = $kernel->getContainer()->get('doctrine');
            self::assertInstanceOf(ManagerRegistry::class, $registry);
            $em = $registry->getManager();
            self::assertInstanceOf(EntityManagerInterface::class, $em);
            $vendors = new VendorRepository($registry);
            $assignments = new VendorUserAssignmentRepository($registry);
            $service = new VendorUserAssignmentService($assignments, $vendors);
            $assignment = $service->assignOwner($id, 42);
            self::assertSame($id, $assignment->getVendorId());
            $em->clear();
            $stored = $assignments->findOneByVendorIdAndUserId($id, 42);
            self::assertInstanceOf(VendorUserAssignmentEntity::class, $stored);
            self::assertSame($id, $stored->getVendorId());
            self::assertTrue($stored->isPrimary());
            try {
                $service->assignOwner($id + 10000, 43);
                self::fail('Unknown vendor must not be created implicitly.');
            } catch (\InvalidArgumentException) {
                self::assertSame(1, $em->getRepository(VendorEntity::class)->count([]));
                self::assertSame(1, $em->getRepository(VendorUserAssignmentEntity::class)->count([]));
            }
            $service->revoke($id, 42);
            $em->clear();
            self::assertSame([], $assignments->findActiveByVendorId($id));
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
