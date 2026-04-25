<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorAttachment;
use App\Vendoring\Entity\VendorMedia;
use App\Vendoring\Entity\VendorMediaAttachment;
use App\Vendoring\Entity\VendorProfileAvatar;
use App\Vendoring\Entity\VendorProfileCover;
use App\Vendoring\Event\VendorAttachmentUploadedEvent;
use App\Vendoring\Event\VendorMediaUploadedEvent;
use App\Vendoring\RepositoryInterface\VendorMediaRepositoryInterface;
use App\Vendoring\ServiceInterface\VendorMediaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorMediaService implements VendorMediaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorMediaRepositoryInterface $mediaRepository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia
    {
        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]) ?? new VendorMedia($vendor);
        $media->update($dto->logoPath, $dto->bannerPath, $dto->gallery);

        $this->em->persist($media);
        $this->em->flush();
        $this->synchronizeProfileAvatar($vendor, $dto->logoPath);
        $this->synchronizeProfileCover($vendor, $dto->bannerPath);
        $this->replaceGalleryAttachments($media, $dto->gallery);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorMediaUploadedEvent($media));

        return $media;
    }


    private function synchronizeProfileAvatar(Vendor $vendor, ?string $logoPath): void
    {
        $repository = $this->em->getRepository(VendorProfileAvatar::class);
        $existing = $repository->findOneBy(['vendor' => $vendor]);

        if (null === $logoPath || '' === trim($logoPath)) {
            if ($existing instanceof VendorProfileAvatar) {
                $this->em->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileAvatar) {
            $existing->update($logoPath);

            return;
        }

        $this->em->persist(new VendorProfileAvatar($vendor, $logoPath));
    }

    private function synchronizeProfileCover(Vendor $vendor, ?string $bannerPath): void
    {
        $repository = $this->em->getRepository(VendorProfileCover::class);
        $existing = $repository->findOneBy(['vendor' => $vendor]);

        if (null === $bannerPath || '' === trim($bannerPath)) {
            if ($existing instanceof VendorProfileCover) {
                $this->em->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileCover) {
            $existing->update($bannerPath);

            return;
        }

        $this->em->persist(new VendorProfileCover($vendor, $bannerPath));
    }

    /** @param list<string>|null $gallery */
    private function replaceGalleryAttachments(VendorMedia $media, ?array $gallery): void
    {
        foreach ($this->em->getRepository(VendorMediaAttachment::class)->findBy(['media' => $media, 'kind' => 'gallery']) as $attachment) {
            if ($attachment instanceof VendorMediaAttachment) {
                $this->em->remove($attachment);
            }
        }

        foreach ($gallery ?? [] as $position => $filePath) {
            $normalized = trim($filePath);

            if ('' === $normalized) {
                continue;
            }

            $this->em->persist(new VendorMediaAttachment($media, 'gallery', $normalized, $position));
        }
    }

    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment
    {
        $attachment = new VendorAttachment($vendor, $dto->title, $dto->filePath, $dto->category);

        $this->em->persist($attachment);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorAttachmentUploadedEvent($attachment));

        return $attachment;
    }
}
