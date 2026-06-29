<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorProfileRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorProfile42BusinessFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorProfileRepositoryInterface $vendorProfileRepository,
    ) {
    }

    public static function getGroups(): array
    {
        return ['vendoring_profile_42'];
    }

    public function load(ObjectManager $manager): void
    {
        $brandName = 'Market Vendor 42 LLC';
        $displayName = 'Vendor 42';
        $website = 'https://vendor-42.vendoring.test';
        $about = 'Vendor 42 is a deterministic fixture profile for vendoring business acceptance checks.';
        $socials = [
            'instagram' => '@vendor_42',
            'linkedin' => 'vendor-42',
            'x' => '@vendor42',
        ];

        $this->seedVendorRow($manager, $brandName);

        $vendor = $this->vendorRepository->find(42);

        if (!$vendor instanceof VendorEntity) {
            return;
        }

        $vendor->rename($brandName);
        $vendor->activate();
        $vendor->changeOwnerUserId(42);
        $manager->persist($vendor);

        $profile = $this->vendorProfileRepository->findOneBy(['vendor' => $vendor]) ?? new VendorProfileEntity($vendor);
        if (!$profile instanceof VendorProfileEntity) {
            $profile = new VendorProfileEntity($vendor);
        }

        $profile->updateProfile(
            displayName: $displayName,
            about: $about,
            website: $website,
            socials: $socials,
            seoTitle: $brandName,
            seoDescription: 'Deterministic Vendor 42 profile used for vendoring fixture validation.',
        );
        $profile->publish();
        $manager->persist($profile);

        $avatarPath = '/fixtures/images/avatar-user.svg';
        $coverPath = '/fixtures/images/vendor-banner.svg';

        $avatar = $manager->getRepository(VendorProfileAvatarEntity::class)->findOneBy(['vendor' => $vendor]);
        if ($avatar instanceof VendorProfileAvatarEntity) {
            $avatar->update($avatarPath);
        } else {
            $avatar = new VendorProfileAvatarEntity($vendor, $avatarPath);
            $manager->persist($avatar);
        }

        $cover = $manager->getRepository(VendorProfileCoverEntity::class)->findOneBy(['vendor' => $vendor]);
        if ($cover instanceof VendorProfileCoverEntity) {
            $cover->update($coverPath);
        } else {
            $cover = new VendorProfileCoverEntity($vendor, $coverPath);
            $manager->persist($cover);
        }

        $manager->flush();
    }

    private function seedVendorRow(ObjectManager $manager, string $brandName): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            return;
        }

        $connection = $manager->getConnection();
        $createdAt = new \DateTimeImmutable();

        $connection->executeStatement(
            <<<'SQL'
INSERT INTO vendor (id, brand_name, owner_user_id, status, created_at)
VALUES (:id, :brand_name, :owner_user_id, :status, :created_at)
ON CONFLICT (id) DO UPDATE SET
    brand_name = EXCLUDED.brand_name,
    owner_user_id = EXCLUDED.owner_user_id,
    status = EXCLUDED.status
SQL,
            [
                'id' => 42,
                'brand_name' => $brandName,
                'owner_user_id' => 42,
                'status' => 'active',
                'created_at' => $createdAt,
            ],
            [
                'id' => Types::INTEGER,
                'brand_name' => Types::STRING,
                'owner_user_id' => Types::INTEGER,
                'status' => Types::STRING,
                'created_at' => Types::DATETIME_IMMUTABLE,
            ],
        );

        $connection->executeStatement(
            <<<'SQL'
SELECT setval(
    pg_get_serial_sequence('vendor', 'id'),
    GREATEST((SELECT COALESCE(MAX(id), 1) FROM vendor), 1),
    true
)
SQL
        );
    }
}
