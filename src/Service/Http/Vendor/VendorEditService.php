<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class VendorEditService extends AbstractCrudEntrypointService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private VendorRepositoryInterface $vendorRepository,
    ) {
    }

    public function get(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $this->resolveVendor($context),
        );
    }

    private function resourcePath(): string
    {
        return 'vendor';
    }

    private function operation(): string
    {
        return 'edit';
    }

    private function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }

    private function resolveVendor(CrudEntrypointContext $context): VendorEntity
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
