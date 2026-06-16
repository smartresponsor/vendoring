<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;

final class VendorIndexService extends AbstractCrudEntrypointService
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
            $this->vendors(),
        );
    }

    private function resourcePath(): string
    {
        return 'vendor';
    }

    private function operation(): string
    {
        return 'index';
    }

    private function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }

    /**
     * @return list<VendorEntity>
     */
    private function vendors(): array
    {
        try {
            return array_values(array_filter(
                $this->vendorRepository->findBy([]),
                static fn (object $entity): bool => $entity instanceof VendorEntity,
            ));
        } catch (\Throwable) {
            return [];
        }
    }
}
