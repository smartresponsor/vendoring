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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Application service for vendor document operations.
 */
final class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * Executes the upload operation for this runtime surface.
     */
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument
    {
        $doc = new VendorDocument($vendor, $dto->type, $dto->filePath);

        if ($dto->expiresAt) {
            $ref = new \ReflectionProperty($doc, 'expiresAt');
            $ref->setAccessible(true);
            $ref->setValue($doc, $dto->expiresAt);
        }

        if ($dto->uploaderId) {
            $ref = new \ReflectionProperty($doc, 'uploaderId');
            $ref->setAccessible(true);
            $ref->setValue($doc, $dto->uploaderId);
        }

        $this->em->persist($doc);
        $this->em->flush();

        $this->dispatcher->dispatch(new DocumentUploadedEvent($doc));

        return $doc;
    }
}
