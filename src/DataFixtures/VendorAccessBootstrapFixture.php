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
            $userId = is_numeric($user['id'] ?? null) ? (int) $user['id'] : 0;
            if ($userId < 1) {
                continue;
            }

            if (!$this->isAdministrativeUser($user) && !$this->isProfessionalUser($user)) {
                $this->deactivateNonProfessionalVendor($manager, $userId);
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
            } elseif ($this->isProfessionalUser($user)) {
                $this->loadProfessionalProfile($manager, $vendor, $user);
            }
        }

        $manager->flush();
    }

    private function deactivateNonProfessionalVendor(EntityManagerInterface $manager, int $userId): void
    {
        $vendor = $manager->getRepository(VendorEntity::class)->findOneBy(['ownerUserId' => $userId]);
        if (!$vendor instanceof VendorEntity) {
            return;
        }

        $vendor->deactivate();
        $manager->persist($vendor);

        $assignment = $manager->getRepository(VendorUserAssignmentEntity::class)->findOneBy([
            'vendor' => $vendor,
            'userId' => $userId,
        ]);
        if ($assignment instanceof VendorUserAssignmentEntity) {
            $assignment->revoke()->clearPrimary();
            $manager->persist($assignment);
        }

        $profile = $manager->getRepository(VendorProfileEntity::class)->findOneBy(['vendor' => $vendor]);
        if ($profile instanceof VendorProfileEntity) {
            $profile->unpublish();
            $manager->persist($profile);
        }
    }

    /** @param array<string, mixed> $user */
    private function isAdministrativeUser(array $user): bool
    {
        $roles = json_decode($this->scalarString($user['roles'] ?? null, '[]'), true);

        return is_array($roles) && in_array('ROLE_ADMIN_BOOTSTRAP', $roles, true);
    }

    /** @param array<string, mixed> $user */
    private function isProfessionalUser(array $user): bool
    {
        $roles = json_decode($this->scalarString($user['roles'] ?? null, '[]'), true);

        return is_array($roles) && in_array('ROLE_PRO', $roles, true);
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
    private function loadProfessionalProfile(EntityManagerInterface $manager, VendorEntity $vendor, array $user): void
    {
        $definition = $this->professionalDefinition($this->scalarString($user['email'] ?? null));
        $vendor->rename($definition['brand']);

        $profile = $manager->getRepository(VendorProfileEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$profile instanceof VendorProfileEntity) {
            $profile = new VendorProfileEntity($vendor);
            $vendor->setProfile($profile);
            $manager->persist($profile);
        }
        $profile
            ->updateProfile(
                displayName: $definition['display'],
                about: $definition['about'],
                website: null,
                socials: null,
                seoTitle: $definition['brand'],
                seoDescription: $definition['about'],
            )
            ->publish();

        $avatar = $manager->getRepository(VendorProfileAvatarEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$avatar instanceof VendorProfileAvatarEntity) {
            $avatar = new VendorProfileAvatarEntity($vendor, $definition['avatar']);
            $manager->persist($avatar);
        } else {
            $avatar->update($definition['avatar']);
        }

        $cover = $manager->getRepository(VendorProfileCoverEntity::class)->findOneBy(['vendor' => $vendor]);
        if (!$cover instanceof VendorProfileCoverEntity) {
            $cover = new VendorProfileCoverEntity($vendor, $definition['cover']);
            $manager->persist($cover);
        } else {
            $cover->update($definition['cover']);
        }
    }

    /** @return array{brand: string, display: string, about: string, avatar: string, cover: string} */
    private function professionalDefinition(string $email): array
    {
        return match (strtolower(trim($email))) {
            'alex.pro@smartresponsor.local' => [
                'brand' => 'OneTasker Houston',
                'display' => 'Alex Morgan',
                'about' => 'Indoor home-service professional focused on TV mounting, fixtures, lighting, ceiling fans, smart locks, doorbells, and appliance installation across west Houston.',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=640&h=640&q=85',
                'cover' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1600&h=600&q=85',
            ],
            'maria.pro@smartresponsor.local' => [
                'brand' => 'Katy Home Care',
                'display' => 'Maria Hernandez',
                'about' => 'Residential cleaning and home-organization professional serving Katy and nearby west Houston neighborhoods.',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=640&h=640&q=85',
                'cover' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=1600&h=600&q=85',
            ],
            'daniel.pro@smartresponsor.local' => [
                'brand' => 'Bayou Assembly & Mounting',
                'display' => 'Daniel Brooks',
                'about' => 'Furniture assembly, wall mounting, art and mirror hanging, shelving, and window-treatment installation for Houston-area homes.',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=640&h=640&q=85',
                'cover' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1600&h=600&q=85',
            ],
            default => [
                'brand' => 'Houston Home Services',
                'display' => 'Houston Home Services',
                'about' => 'Residential indoor home-service professional serving the Houston metro area.',
                'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=640&h=640&q=85',
                'cover' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&h=600&q=85',
            ],
        };
    }

    /** @param array<string, mixed> $user */
    private function brandName(array $user, int $userId): string
    {
        $email = trim($this->scalarString($user['email'] ?? null));
        if ($this->isProfessionalUser($user)) {
            return $this->professionalDefinition($email)['brand'];
        }

        $displayName = trim($this->scalarString($user['display_name'] ?? null));
        if ('' !== $displayName) {
            return $displayName;
        }

        if ('' !== $email) {
            return strstr($email, '@', true) ?: $email;
        }

        return 'Vendor '.$userId;
    }

    private function scalarString(mixed $value, string $default = ''): string
    {
        return is_scalar($value) ? (string) $value : $default;
    }
}
