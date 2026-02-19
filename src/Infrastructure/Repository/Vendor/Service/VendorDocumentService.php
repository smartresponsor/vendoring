<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Service;


use App\RepositoryInterface\Vendor\Service\VendorDocumentServiceInterface;
use App\DTO\Vendor\VendorDocumentDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorDocument;
use App\Event\Vendor\DocumentUploadedEvent;
use App\Repository\Vendor\VendorDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorDocumentService
    implements VendorDocumentServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface   $em,
        private readonly VendorDocumentRepository $repository,
        private readonly EventDispatcherInterface $dispatcher
    )
    {
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
