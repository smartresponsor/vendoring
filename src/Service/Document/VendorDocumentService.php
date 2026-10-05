<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Document;

use App\Vendoring\DTO\VendorDocumentDTO;
use App\Vendoring\Entity\Vendor\VendorDocumentAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\VendorDocumentUploadedEvent;
use App\Vendoring\RepositoryInterface\VendorDocumentAttachmentRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorDocumentRepositoryInterface;
use App\Vendoring\ServiceInterface\Document\VendorDocumentServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private VendorDocumentRepositoryInterface $documentRepository,
        private VendorDocumentAttachmentRepositoryInterface $attachmentRepository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    private function synchronizeDocumentAttachment(VendorDocumentEntity $document, string $filePath): void
    {
        $existing = $this->attachmentRepository->findOneBy(['document' => $document]);

        if ($existing instanceof VendorDocumentAttachmentEntity) {
            $existing->update($filePath);
            $this->attachmentRepository->save($existing, true);

            return;
        }

        $this->attachmentRepository->save(new VendorDocumentAttachmentEntity($document, $filePath), true);
    }

    public function upload(VendorEntity $vendor, VendorDocumentDTO $dto): VendorDocumentEntity
    {
        $document = new VendorDocumentEntity($vendor, $dto->type, $dto->filePath);
        $document->assignMetadata($dto->expiresAt, $dto->uploaderId);

        $this->documentRepository->save($document, true);
        $this->synchronizeDocumentAttachment($document, $dto->filePath);

        $this->dispatcher->dispatch(new VendorDocumentUploadedEvent($document));

        return $document;
    }
}
