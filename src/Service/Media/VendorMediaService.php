<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Media;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorMediaAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Event\VendorAttachmentUploadedEvent;
use App\Vendoring\Event\VendorMediaUploadedEvent;
use App\Vendoring\RepositoryInterface\VendorAttachmentRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorMediaAttachmentRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorMediaRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileAvatarRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorProfileCoverRepositoryInterface;
use App\Vendoring\ServiceInterface\Media\VendorMediaServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorMediaService implements VendorMediaServiceInterface
{
    public function __construct(
        private VendorMediaRepositoryInterface $mediaRepository,
        private VendorProfileAvatarRepositoryInterface $avatarRepository,
        private VendorProfileCoverRepositoryInterface $coverRepository,
        private VendorMediaAttachmentRepositoryInterface $mediaAttachmentRepository,
        private VendorAttachmentRepositoryInterface $attachmentRepository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsertMedia(VendorEntity $vendor, VendorMediaUploadDTO $dto): VendorMediaEntity
    {
        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]) ?? new VendorMediaEntity($vendor);
        $media->update($dto->logoPath, $dto->bannerPath, $dto->gallery);

        $this->mediaRepository->save($media);
        $this->synchronizeProfileAvatar($vendor, $dto->logoPath);
        $this->synchronizeProfileCover($vendor, $dto->bannerPath);
        $this->replaceGalleryAttachments($media, $dto->gallery);
        $this->mediaRepository->flush();

        $this->dispatcher->dispatch(new VendorMediaUploadedEvent($media));

        return $media;
    }

    private function synchronizeProfileAvatar(VendorEntity $vendor, ?string $logoPath): void
    {
        $existing = $this->avatarRepository->findOneBy(['vendor' => $vendor]);

        if (null === $logoPath || '' === trim($logoPath)) {
            if ($existing instanceof VendorProfileAvatarEntity) {
                $this->avatarRepository->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileAvatarEntity) {
            $existing->update($logoPath);

            return;
        }

        $this->avatarRepository->save(new VendorProfileAvatarEntity($vendor, $logoPath));
    }

    private function synchronizeProfileCover(VendorEntity $vendor, ?string $bannerPath): void
    {
        $existing = $this->coverRepository->findOneBy(['vendor' => $vendor]);

        if (null === $bannerPath || '' === trim($bannerPath)) {
            if ($existing instanceof VendorProfileCoverEntity) {
                $this->coverRepository->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorProfileCoverEntity) {
            $existing->update($bannerPath);

            return;
        }

        $this->coverRepository->save(new VendorProfileCoverEntity($vendor, $bannerPath));
    }

    /** @param list<string>|null $gallery */
    private function replaceGalleryAttachments(VendorMediaEntity $media, ?array $gallery): void
    {
        foreach ($this->mediaAttachmentRepository->findBy(['media' => $media, 'kind' => 'gallery']) as $attachment) {
            $this->mediaAttachmentRepository->remove($attachment);
        }

        foreach ($gallery ?? [] as $position => $filePath) {
            $normalized = trim($filePath);

            if ('' === $normalized) {
                continue;
            }

            $this->mediaAttachmentRepository->save(new VendorMediaAttachmentEntity($media, 'gallery', $normalized, $position));
        }
    }

    public function uploadAttachment(VendorEntity $vendor, VendorAttachmentDTO $dto): VendorAttachmentEntity
    {
        $attachment = new VendorAttachmentEntity($vendor, $dto->title, $dto->filePath, $dto->category);

        $this->attachmentRepository->save($attachment, true);

        $this->dispatcher->dispatch(new VendorAttachmentUploadedEvent($attachment));

        return $attachment;
    }
}
