<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorAttachmentDTO;
use App\DTO\VendorMediaUploadDTO;
use App\Entity\Vendor;
use App\Entity\VendorAttachment;
use App\Entity\VendorMedia;
use App\Event\VendorAttachmentUploadedEvent;
use App\Event\VendorMediaUploadedEvent;
use App\RepositoryInterface\VendorMediaRepositoryInterface;
use App\ServiceInterface\VendorMediaServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorMediaService implements VendorMediaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorMediaRepositoryInterface $mediaRepository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia
    {
        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]) ?? new VendorMedia($vendor);

        $ref = new \ReflectionClass($media);

        foreach (['logoPath', 'bannerPath', 'gallery'] as $prop) {
            if (property_exists($media, $prop) && isset($dto->{$prop})) {
                $rp = $ref->getProperty($prop);
                $rp->setValue($media, $dto->{$prop});
            }
        }

        $this->em->persist($media);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorMediaUploadedEvent($media));

        return $media;
    }

    public function uploadAttachment(Vendor $vendor, VendorAttachmentDTO $dto): VendorAttachment
    {
        $att = new VendorAttachment($vendor, $dto->title, $dto->filePath, $dto->category);

        $this->em->persist($att);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorAttachmentUploadedEvent($att));

        return $att;
    }
}
