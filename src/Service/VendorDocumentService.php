<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\DTO\VendorDocumentDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorDocument;
use App\Vendoring\Entity\VendorDocumentAttachment;
use App\Vendoring\Event\DocumentUploadedEvent;
use App\Vendoring\ServiceInterface\VendorDocumentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $dispatcher,
    ) {}


    private function synchronizeDocumentAttachment(VendorDocument $document, string $filePath): void
    {
        $repository = $this->em->getRepository(VendorDocumentAttachment::class);
        $existing = $repository->findOneBy(['document' => $document]);

        if ($existing instanceof VendorDocumentAttachment) {
            $existing->update($filePath);

            return;
        }

        $this->em->persist(new VendorDocumentAttachment($document, $filePath));
    }

    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument
    {
        $document = new VendorDocument($vendor, $dto->type, $dto->filePath);
        $document->assignMetadata($dto->expiresAt, $dto->uploaderId);

        $this->em->persist($document);
        $this->em->flush();
        $this->synchronizeDocumentAttachment($document, $dto->filePath);
        $this->em->flush();

        $this->dispatcher->dispatch(new DocumentUploadedEvent($document));

        return $document;
    }
}
