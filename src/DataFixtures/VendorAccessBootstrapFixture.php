<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorAccessBootstrapFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['vendoring_access_bootstrap'];
    }

    public function load(ObjectManager $manager): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            return;
        }

        $users = $manager->getConnection()->fetchAllAssociative(
            'SELECT id, email, display_name, roles FROM access ORDER BY id',
        );

        foreach ($users as $user) {
            $userId = (int) ($user['id'] ?? 0);
            if ($userId < 1) {
                continue;
            }

            $vendor = $manager->getRepository(VendorEntity::class)->findOneBy(['ownerUserId' => $userId]);
            if (!$vendor instanceof VendorEntity) {
                $vendor = new VendorEntity($this->brandName($user, $userId), $userId);
                $vendor->activate();
                $manager->persist($vendor);
                $manager->flush();
            }

            $assignment = $manager->getRepository(VendorUserAssignmentEntity::class)->findOneBy([
                'vendor' => $vendor,
                'userId' => $userId,
            ]);

            if (!$assignment instanceof VendorUserAssignmentEntity) {
                $assignment = new VendorUserAssignmentEntity(
                    vendorId: $vendor,
                    userId: $userId,
                    role: 'owner',
                    status: 'active',
                    isPrimary: true,
                );
                $manager->persist($assignment);
            } else {
                $assignment->changeRole('owner')->activate()->markPrimary();
            }

            if ($this->isAdministrativeUser($user)) {
                $this->loadAdministrativeProfile($manager, $vendor);
            }
        }

        $manager->flush();
    }

    /** @param array<string, mixed> $user */
    private function isAdministrativeUser(array $user): bool
    {
        $roles = json_decode((string) ($user['roles'] ?? '[]'), true);

        return is_array($roles) && in_array('ROLE_ADMIN_BOOTSTRAP', $roles, true);
    }

    private function loadAdministrativeProfile(EntityManagerInterface $manager, VendorEntity $vendor): void
    {
        $profile = $manager->getRepository(VendorProfileEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$profile instanceof VendorProfileEntity) {
            $profile = new VendorProfileEntity($vendor);
            $vendor->setProfile($profile);
            $manager->persist($profile);
        }
        $profile
            ->updateProfile(
                displayName: 'SmartResponsor Platform Administration',
                about: 'Official administrative vendor profile for SmartResponsor. We build and operate an AI-assisted marketplace platform connecting customers, service providers, products, projects, and business workflows in one coordinated environment.',
                website: 'https://smartresponsor.com',
                socials: [
                    'linkedin' => 'https://www.linkedin.com/company/smartresponsor',
                    'facebook' => 'https://www.facebook.com/smartresponsor',
                    'github' => 'https://github.com/smartresponsor',
                ],
                seoTitle: 'SmartResponsor Platform Administration',
                seoDescription: 'Official SmartResponsor administrative vendor profile, platform updates, services, and marketplace activity.',
            )
            ->publish();

        $media = $manager->getRepository(VendorMediaEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$media instanceof VendorMediaEntity) {
            $media = new VendorMediaEntity($vendor);
            $vendor->setMedia($media);
            $manager->persist($media);
        }
        $media->update(
            logoPath: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85',
            bannerPath: 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85',
            gallery: [],
        );

        $avatar = $manager->getRepository(VendorProfileAvatarEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$avatar instanceof VendorProfileAvatarEntity) {
            $avatar = new VendorProfileAvatarEntity($vendor, 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85');
            $manager->persist($avatar);
        } else {
            $avatar->update('https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85');
        }

        $cover = $manager->getRepository(VendorProfileCoverEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$cover instanceof VendorProfileCoverEntity) {
            $cover = new VendorProfileCoverEntity($vendor, 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85');
            $manager->persist($cover);
        } else {
            $cover->update('https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85');
        }
    }

    /** @param array<string, mixed> $user */
    private function brandName(array $user, int $userId): string
    {
        $displayName = trim((string) ($user['display_name'] ?? ''));
        if ('' !== $displayName) {
            return $displayName;
        }

        $email = trim((string) ($user['email'] ?? ''));
        if ('' !== $email) {
            return strstr($email, '@', true) ?: $email;
        }

        return 'Vendor '.$userId;
    }
}
