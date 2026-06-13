<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Document;

use App\Vendoring\DTO\VendorDocumentDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentAttachmentEntity;
use App\Vendoring\Event\Vendor\VendorDocumentUploadedEvent;
use App\Vendoring\ServiceInterface\Document\VendorDocumentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $dispatcher,
    ) {}


    private function synchronizeDocumentAttachment(VendorDocumentEntity $document, string $filePath): void
    {
        $repository = $this->em->getRepository(VendorDocumentAttachmentEntity::class);
        $existing = $repository->findOneBy(['document' => $document]);

        if ($existing instanceof VendorDocumentAttachmentEntity) {
            $existing->update($filePath);

            return;
        }

        $this->em->persist(new VendorDocumentAttachmentEntity($document, $filePath));
    }

    public function upload(VendorEntity $vendor, VendorDocumentDTO $dto): VendorDocumentEntity
    {
        $document = new VendorDocumentEntity($vendor, $dto->type, $dto->filePath);
        $document->assignMetadata($dto->expiresAt, $dto->uploaderId);

        $this->em->persist($document);
        $this->em->flush();
        $this->synchronizeDocumentAttachment($document, $dto->filePath);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorDocumentUploadedEvent($document));

        return $document;
    }
}
