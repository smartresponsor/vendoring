<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\RepositoryInterface\VendorProfileAvatarRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileCoverRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorProfile42BusinessFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorProfileRepositoryInterface $vendorProfileRepository,
        private readonly VendorProfileAvatarRepositoryInterface $avatarRepository,
        private readonly VendorProfileCoverRepositoryInterface $coverRepository,
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

        $this->vendorRepository->seedDeterministicVendor(42, $brandName, 42);

        $vendor = $this->vendorRepository->find(42);

        if (!$vendor instanceof VendorEntity) {
            return;
        }

        $vendor->rename($brandName);
        $vendor->activate();
        $vendor->changeOwnerUserId(42);
        $this->vendorRepository->save($vendor);

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
        $this->vendorProfileRepository->save($profile);

        $avatarPath = '/fixtures/images/avatar-user.svg';
        $coverPath = '/fixtures/images/vendor-banner.svg';

        $avatar = $this->avatarRepository->findOneBy(['vendor' => $vendor]);
        if ($avatar instanceof VendorProfileAvatarEntity) {
            $avatar->update($avatarPath);
        } else {
            $avatar = new VendorProfileAvatarEntity($vendor, $avatarPath);
            $this->avatarRepository->save($avatar);
        }

        $cover = $this->coverRepository->findOneBy(['vendor' => $vendor]);
        if ($cover instanceof VendorProfileCoverEntity) {
            $cover->update($coverPath);
        } else {
            $cover = new VendorProfileCoverEntity($vendor, $coverPath);
            $this->coverRepository->save($cover);
        }

        $this->vendorRepository->save($vendor, true);
    }
}
