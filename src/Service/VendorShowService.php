<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Cruding\DTO\Entrypoint\CrudServiceContextDTO;
use App\Cruding\ValueObject\Resource\CrudResourceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class VendorShowService extends VendorAbstractCrudRouteService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private VendorRepositoryInterface $vendorRepository,
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

    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'show';
    }

    protected function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
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
}
