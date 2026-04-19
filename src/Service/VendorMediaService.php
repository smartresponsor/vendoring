<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\DTO\VendorAttachmentDTO;
use App\Vendoring\DTO\VendorMediaUploadDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorAttachment;
use App\Vendoring\Entity\VendorMedia;
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

        $this->dispatcher->dispatch(new VendorMediaUploadedEvent($media));

        return $media;
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
