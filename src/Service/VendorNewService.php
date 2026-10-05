<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Cruding\DTO\Entrypoint\CrudServiceContextDTO;
use App\Cruding\ValueObject\Resource\CrudResourceContract;

final class VendorNewService extends VendorAbstractCrudRouteService
{
    public function __construct(private VendorHttpRouteResponseService $responseService)
    {
    }

    public function get(CrudServiceContextDTO $context): CrudResourceContract
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
