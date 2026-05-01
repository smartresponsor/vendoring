<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Media;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorMediaAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Event\Vendor\VendorAttachmentUploadedEvent;
use App\Vendoring\Event\Vendor\VendorMediaUploadedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorMediaRepositoryInterface;
use App\Vendoring\ServiceInterface\Media\VendorMediaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorMediaService implements VendorMediaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorMediaRepositoryInterface $mediaRepository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function upsertMedia(VendorEntity $vendor, VendorMediaUploadDTO $dto): VendorMediaEntity
    {
        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]) ?? new VendorMediaEntity($vendor);
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


    private function synchronizeProfileAvatar(VendorEntity $vendor, ?string $logoPath): void
    {
        $repository = $this->em->getRepository(VendorProfileAvatarEntity::class);
        $existing = $repository->findOneBy(['vendor' => $vendor]);

        if (null === $logoPath || '' === trim($logoPath)) {
            if ($existing instanceof VendorProfileAvatarEntity) {
                $this->em->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileAvatarEntity) {
            $existing->update($logoPath);

            return;
        }

        $this->em->persist(new VendorProfileAvatarEntity($vendor, $logoPath));
    }

    private function synchronizeProfileCover(VendorEntity $vendor, ?string $bannerPath): void
    {
        $repository = $this->em->getRepository(VendorProfileCoverEntity::class);
        $existing = $repository->findOneBy(['vendor' => $vendor]);

        if (null === $bannerPath || '' === trim($bannerPath)) {
            if ($existing instanceof VendorProfileCoverEntity) {
                $this->em->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileCoverEntity) {
            $existing->update($bannerPath);

            return;
        }

        $this->em->persist(new VendorProfileCoverEntity($vendor, $bannerPath));
    }

    /** @param list<string>|null $gallery */
    private function replaceGalleryAttachments(VendorMediaEntity $media, ?array $gallery): void
    {
        foreach ($this->em->getRepository(VendorMediaAttachmentEntity::class)->findBy(['media' => $media, 'kind' => 'gallery']) as $attachment) {
            if ($attachment instanceof VendorMediaAttachmentEntity) {
                $this->em->remove($attachment);
            }
        }

        foreach ($gallery ?? [] as $position => $filePath) {
            $normalized = trim($filePath);

            if ('' === $normalized) {
                continue;
            }

            $this->em->persist(new VendorMediaAttachmentEntity($media, 'gallery', $normalized, $position));
        }
    }

    public function uploadAttachment(VendorEntity $vendor, VendorAttachmentDTO $dto): VendorAttachmentEntity
    {
        $attachment = new VendorAttachmentEntity($vendor, $dto->title, $dto->filePath, $dto->category);

        $this->em->persist($attachment);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorAttachmentUploadedEvent($attachment));

        return $attachment;
    }
}
