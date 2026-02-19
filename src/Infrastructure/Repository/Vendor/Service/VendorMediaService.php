<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Service;


use App\RepositoryInterface\Vendor\Service\VendorMediaServiceInterface;
use App\DTO\Vendor\VendorMediaUploadDTO;
use App\DTO\Vendor\VendorAttachmentDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorMedia;
use App\Entity\Vendor\VendorAttachment;
use App\Event\Vendor\VendorMediaUploadedEvent;
use App\Event\Vendor\VendorAttachmentUploadedEvent;
use App\Repository\Vendor\VendorMediaRepository;
use App\Repository\Vendor\VendorAttachmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorMediaService
    implements VendorMediaServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface     $em,
        private readonly VendorMediaRepository      $mediaRepository,
        private readonly VendorAttachmentRepository $attachmentRepository,
        private readonly EventDispatcherInterface   $dispatcher
    )
    {
    }

    public function upsertMedia(Vendor $vendor, VendorMediaUploadDTO $dto): VendorMedia
    {
        $media = $this->mediaRepository->findOneBy(['vendor' => $vendor]) ?? new VendorMedia($vendor);

        $ref = new \ReflectionClass($media);
        foreach (['logoPath', 'bannerPath', 'gallery'] as $prop) {
            if (property_exists($media, $prop) && isset($dto->{$prop})) {
                $rp = $ref->getProperty($prop);
                $rp->setAccessible(true);
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
