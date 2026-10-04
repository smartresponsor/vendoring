<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Cruding\DTO\Entrypoint\CrudServiceContextDTO;
use App\Cruding\ValueObject\Resource\CrudResourceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Attachment\VendorAttachmentOwnerPurgeServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class VendorDeleteService extends VendorAbstractCrudRouteService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private VendorRepositoryInterface $vendorRepository,
        private VendorAttachmentOwnerPurgeServiceInterface $attachmentOwnerPurgeService,
    ) {
    }

    public function get(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $this->resolveVendor($context),
        );
    }

    public function post(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->deleteVendor($context);
    }

    public function delete(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->deleteVendor($context);
    }

    private function deleteVendor(CrudServiceContextDTO $context): CrudResourceContract
    {
        $vendor = $this->resolveVendor($context);
        $vendorId = (string) ($context->identifierValue() ?? $vendor->getId());
        $this->attachmentOwnerPurgeService->purge('vendor', $vendorId);
        $this->vendorRepository->remove($vendor, true);

        return $this->responseService->mutation(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            [
                'id' => $vendor->getId(),
                'brandName' => $vendor->getBrandName(),
            ],
        );
    }

    private function resolveVendor(CrudServiceContextDTO $context): VendorEntity
    {
        if ($context->object instanceof VendorEntity) {
            return $context->object;
        }

        $identifier = $context->identifierValue();
        if (null === $identifier || '' === (string) $identifier) {
            throw new NotFoundHttpException('vendor_identifier_required');
        }

        $vendor = $this->vendorRepository->find($identifier);
        if (!$vendor instanceof VendorEntity) {
            throw new NotFoundHttpException('vendor_not_found');
        }

        return $vendor;
    }

    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'delete';
    }

    protected function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }
}
