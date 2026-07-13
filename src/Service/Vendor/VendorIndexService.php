<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudServiceContext;
use App\Cruding\Value\Resource\CrudResourceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;

final class VendorIndexService extends AbstractVendorCrudRouteService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private VendorRepositoryInterface $vendorRepository,
    ) {
    }

    public function get(CrudServiceContext $context): CrudResourceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $this->vendors(),
        );
    }

    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'index';
    }

    protected function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }

    /**
     * @return list<VendorEntity>
     */
    private function vendors(): array
    {
        try {
            return $this->vendorRepository->findBy([]);
        } catch (\Throwable) {
            return [];
        }
    }
}
