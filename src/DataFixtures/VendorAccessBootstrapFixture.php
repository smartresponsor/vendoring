<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\VendorMediaRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileAvatarRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileCoverRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorAccessBootstrapFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private readonly VendorProfileRepositoryInterface $profileRepository,
        private readonly VendorMediaRepositoryInterface $mediaRepository,
        private readonly VendorProfileAvatarRepositoryInterface $avatarRepository,
        private readonly VendorProfileCoverRepositoryInterface $coverRepository,
    ) {
    }

    public static function getGroups(): array
    {
        return ['vendoring_access_bootstrap'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->run();
    }

    public function run(): void
    {
        $users = $this->vendorRepository->findAccessBootstrapRows();

        foreach ($users as $user) {
            $userId = is_numeric($user['id'] ?? null) ? (int) $user['id'] : 0;
            if ($userId < 1) {
                continue;
            }

            if (!$this->isAdministrativeUser($user) && !$this->isProfessionalUser($user)) {
                $this->deactivateNonProfessionalVendor($userId);
                continue;
            }

            $vendor = $this->vendorRepository->findOneBy(['ownerUserId' => $userId]);
            if (!$vendor instanceof VendorEntity) {
                $vendor = new VendorEntity($this->brandName($user, $userId), $userId);
                $vendor->activate();
                $this->vendorRepository->save($vendor, true);
            }

            $assignment = $this->assignmentRepository->findOneBy([
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
            } else {
                $assignment->changeRole('owner')->activate()->markPrimary();
            }
            $this->assignmentRepository->save($assignment);

            if ($this->isAdministrativeUser($user)) {
                $this->loadAdministrativeProfile($vendor);
            } elseif ($this->isProfessionalUser($user)) {
                $this->loadProfessionalProfile($vendor, $user);
            }

            $this->vendorRepository->save($vendor, true);
        }
    }

    private function deactivateNonProfessionalVendor(int $userId): void
    {
        $vendor = $this->vendorRepository->findOneBy(['ownerUserId' => $userId]);
        if (!$vendor instanceof VendorEntity) {
            return;
        }

        $vendor->deactivate();

        $assignment = $this->assignmentRepository->findOneBy([
            'vendor' => $vendor,
            'userId' => $userId,
        ]);
        if ($assignment instanceof VendorUserAssignmentEntity) {
            $assignment->revoke()->clearPrimary();
            $this->assignmentRepository->save($assignment);
        }

        $profile = $this->profileRepository->findOneBy(['vendor' => $vendor]);
        if ($profile instanceof VendorProfileEntity) {
            $profile->unpublish();
            $this->profileRepository->save($profile);
        }

        $this->vendorRepository->save($vendor, true);
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

    private function loadAdministrativeProfile(VendorEntity $vendor): void
    {
        $profile = $this->profileRepository->findOneBy(['vendor' => $vendor]);
        if (!$profile instanceof VendorProfileEntity) {
            $profile = new VendorProfileEntity($vendor);
            $vendor->setProfile($profile);
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
        $this->profileRepository->save($profile);

        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]);
        if (!$media instanceof VendorMediaEntity) {
            $media = new VendorMediaEntity($vendor);
            $vendor->setMedia($media);
        }
        $media->update(
            logoPath: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85',
            bannerPath: 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85',
            gallery: [],
        );
        $this->mediaRepository->save($media);

        $avatar = $this->avatarRepository->findOneBy(['vendor' => $vendor]);
        if (!$avatar instanceof VendorProfileAvatarEntity) {
            $avatar = new VendorProfileAvatarEntity($vendor, 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85');
        } else {
            $avatar->update('https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=800&h=800&q=85');
        }
        $this->avatarRepository->save($avatar);

        $cover = $this->coverRepository->findOneBy(['vendor' => $vendor]);
        if (!$cover instanceof VendorProfileCoverEntity) {
            $cover = new VendorProfileCoverEntity($vendor, 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85');
        } else {
            $cover->update('https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1640&h=624&q=85');
        }
        $this->coverRepository->save($cover);
    }

    /** @param array<string, mixed> $user */
    private function loadProfessionalProfile(VendorEntity $vendor, array $user): void
    {
        $definition = $this->professionalDefinition($this->scalarString($user['email'] ?? null));
        $vendor->rename($definition['brand']);

        $profile = $this->profileRepository->findOneBy(['vendor' => $vendor]);
        if (!$profile instanceof VendorProfileEntity) {
            $profile = new VendorProfileEntity($vendor);
            $vendor->setProfile($profile);
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
        $this->profileRepository->save($profile);

        $avatar = $this->avatarRepository->findOneBy(['vendor' => $vendor]);
        if (!$avatar instanceof VendorProfileAvatarEntity) {
            $avatar = new VendorProfileAvatarEntity($vendor, $definition['avatar']);
        } else {
            $avatar->update($definition['avatar']);
        }
        $this->avatarRepository->save($avatar);

        $cover = $this->coverRepository->findOneBy(['vendor' => $vendor]);
        if (!$cover instanceof VendorProfileCoverEntity) {
            $cover = new VendorProfileCoverEntity($vendor, $definition['cover']);
        } else {
            $cover->update($definition['cover']);
        }
        $this->coverRepository->save($cover);
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
