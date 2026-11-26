<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Integration\Runtime;

use App\Vendoring\DataFixtures\VendorProfile42BusinessFixture;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\Repository\VendorProfileAvatarRepository;
use App\Vendoring\Repository\VendorProfileCoverRepository;
use App\Vendoring\Repository\VendorProfileRepository;
use App\Vendoring\Repository\VendorRepository;
use App\Vendoring\Tests\Support\Runtime\KernelRuntimeHarness;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;

final class VendorProfile42FixturePersistenceTest extends TestCase
{
    public function testFixtureUsesMappedColumnsAndIsIdempotent(): void
    {
        $kernel = KernelRuntimeHarness::createKernelWithFreshSqliteDatabase(dirname(__DIR__, 3));
        try {
            $registry = $kernel->getContainer()->get('doctrine');
            self::assertInstanceOf(ManagerRegistry::class, $registry);
            $em = $registry->getManager();
            self::assertInstanceOf(EntityManagerInterface::class, $em);
            $vendors = new VendorRepository($registry);
            $fixture = new VendorProfile42BusinessFixture($vendors, new VendorProfileRepository($registry), new VendorProfileAvatarRepository($registry), new VendorProfileCoverRepository($registry));
            $fixture->load($em);
            $em->clear();
            $vendor = $vendors->find(42);
            self::assertInstanceOf(VendorEntity::class, $vendor);
            self::assertSame('Market Vendor 42 LLC', $vendor->getBrandName());
            self::assertSame($vendor->getBrandName(), $vendor->getFirstTitle());
            self::assertSame(42, $vendor->getOwnerUserId());
            $uuid = $vendor->getObjectUuid();
            $fixture->load($em);
            $em->clear();
            $vendor = $vendors->find(42);
            self::assertInstanceOf(VendorEntity::class, $vendor);
            self::assertSame($uuid, $vendor->getObjectUuid());
            foreach ([VendorProfileEntity::class, VendorProfileAvatarEntity::class, VendorProfileCoverEntity::class] as $class) {
                self::assertSame(1, $em->getRepository($class)->count(['vendor' => $vendor]));
            }
            self::assertSame('published', $vendor->getProfile()?->getPublicProfileStatus());
            self::assertSame('/fixtures/images/avatar-user.svg', $vendor->getAvatarPath());
            self::assertSame('/fixtures/images/vendor-banner.svg', $vendor->getCoverPath());
        } finally {
            KernelRuntimeHarness::cleanupRuntimeState($kernel);
        }
    }
}
