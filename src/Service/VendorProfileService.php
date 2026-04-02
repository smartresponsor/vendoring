<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorProfileDTO;
use App\Entity\Vendor;
use App\Entity\VendorProfile;
use App\Event\VendorProfileUpdatedEvent;
use App\RepositoryInterface\VendorProfileRepositoryInterface;
use App\ServiceInterface\VendorProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorProfileService implements VendorProfileServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorProfileRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile
    {
        $profile = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorProfile($vendor);
        $profile->updateProfile(
            displayName: $this->normalizeNullableString($dto->displayName),
            about: $this->normalizeNullableString($dto->about),
            website: $this->normalizeNullableString($dto->website),
            socials: $this->normalizeSocials($dto->socials),
            seoTitle: $this->normalizeNullableString($dto->seoTitle),
            seoDescription: $this->normalizeNullableString($dto->seoDescription),
        );
        $this->applyPublicationAction($profile, $dto->publicationAction);

        $this->em->persist($profile);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorProfileUpdatedEvent($profile));

        return $profile;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }

    /**
     * @param array<string, string>|null $socials
     *
     * @return array<string, string>|null
     */
    private function normalizeSocials(?array $socials): ?array
    {
        if (null === $socials) {
            return null;
        }

        $normalized = [];

        foreach ($socials as $network => $url) {
            $network = trim((string) $network);
            $url = trim($url);

            if ('' === $network || '' === $url) {
                continue;
            }

            $normalized[$network] = $url;
        }

        return [] === $normalized ? null : $normalized;
    }

    private function applyPublicationAction(VendorProfile $profile, ?string $publicationAction): void
    {
        if (null === $publicationAction) {
            return;
        }

        match ($publicationAction) {
            'save_draft' => $profile->markPublicProfileDraft(),
            'publish' => $this->publishProfile($profile),
            'unpublish' => $profile->markPublicProfileDraft(),
            default => throw new \InvalidArgumentException('publication_action_invalid'),
        };
    }

    private function publishProfile(VendorProfile $profile): void
    {
        if (!$this->isPublishable($profile)) {
            throw new \InvalidArgumentException('public_profile_incomplete');
        }

        $profile->markPublicProfilePublished();
    }

    private function isPublishable(VendorProfile $profile): bool
    {
        return null !== $profile->getDisplayName()
            && null !== $profile->getAbout()
            && null !== $profile->getWebsite()
            && null !== $profile->getSeoTitle()
            && null !== $profile->getSeoDescription()
            && [] !== ($profile->getSocials() ?? []);
    }
}
