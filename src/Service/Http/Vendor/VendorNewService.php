<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudServiceContext;
use App\Cruding\Value\Resource\CrudResourceContract;

final class VendorNewService extends AbstractVendorCrudRouteService
{
    public function __construct(private VendorHttpRouteResponseService $responseService)
    {
    }

    public function get(CrudServiceContext $context): CrudResourceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            [
                'brandName' => '',
                'ownerUserId' => null,
            ],
        );
    }

    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'new';
    }

    protected function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }
}
