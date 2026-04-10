<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorDocumentDTO;
use App\Entity\Vendor;
use App\Entity\VendorDocument;
use App\Event\DocumentUploadedEvent;
use App\ServiceInterface\VendorDocumentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /** @throws ORMException|OptimisticLockException */
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument
    {
        $document = new VendorDocument($vendor, $dto->type, $dto->filePath);
        $document->assignMetadata($dto->expiresAt, $dto->uploaderId);

        $this->em->persist($document);
        $this->em->flush();

        $this->dispatcher->dispatch(new DocumentUploadedEvent($document));

        return $document;
    }
}
