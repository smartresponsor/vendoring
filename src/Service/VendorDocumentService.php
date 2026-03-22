<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorDocumentDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorDocument;
use App\Event\DocumentUploadedEvent;
use App\ServiceInterface\VendorDocumentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorDocumentService implements VendorDocumentServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

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
