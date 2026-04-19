<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\DTO\VendorProfileDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorProfile;
use App\Vendoring\Event\VendorProfileUpdatedEvent;
use App\Vendoring\RepositoryInterface\VendorProfileRepositoryInterface;
use App\Vendoring\ServiceInterface\VendorProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorProfileService implements VendorProfileServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorProfileRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile
    {
        $profile = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorProfile($vendor);
        $profile->updateContent(
            $this->normalizeNullableString($dto->displayName),
            $this->normalizeNullableString($dto->about),
            $this->normalizeNullableString($dto->website),
        );
        $profile->replaceSocials($this->normalizeSocials($dto->socials));
        $profile->updateSeo(
            $this->normalizeNullableString($dto->seoTitle),
            $this->normalizeNullableString($dto->seoDescription),
        );

        if ('publish' === $dto->publicationAction) {
            if (!$this->isPublishable($profile)) {
                throw new InvalidArgumentException('public_profile_incomplete');
            }

            $profile->publish();
        } elseif ('unpublish' === $dto->publicationAction) {
            $profile->unpublish();
        }

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

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    /**
     * @param array<string, string>|null $socials
     * @return array<string, string>|null
     */
    private function normalizeSocials(?array $socials): ?array
    {
        if (null === $socials) {
            return null;
        }

        $normalized = [];
        foreach ($socials as $network => $url) {
            $normalizedNetwork = trim($network);
            $normalizedUrl = trim($url);

            if ('' === $normalizedNetwork || '' === $normalizedUrl) {
                continue;
            }

            $normalized[$normalizedNetwork] = $normalizedUrl;
        }

        return [] === $normalized ? null : $normalized;
    }

    private function isPublishable(VendorProfile $profile): bool
    {
        return null !== $profile->getDisplayName()
            && null !== $profile->getAbout()
            && null !== $profile->getWebsite()
            && null !== $profile->getSeoTitle()
            && null !== $profile->getSeoDescription();
    }
}
