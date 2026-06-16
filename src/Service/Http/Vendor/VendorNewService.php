<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;

final class VendorNewService extends AbstractCrudEntrypointService
{
    public function __construct(private VendorHttpRouteResponseService $responseService)
    {
    }

    public function get(CrudEntrypointContext $context): CrudSurfaceContract
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

    private function resourcePath(): string
    {
        return 'vendor';
    }

    private function operation(): string
    {
        return 'new';
    }

    private function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }
}
