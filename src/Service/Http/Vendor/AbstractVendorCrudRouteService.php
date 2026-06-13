<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\ServiceInterface\Crud\VendorCrudServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractVendorCrudRouteService extends AbstractCrudEntrypointService
{
    public function __construct(
        private readonly VendorHttpRouteResponseService $responseService,
        private readonly VendorCrudServiceInterface $vendorCrudService,
    ) {
    }

    public function get(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return match ($this->operation()) {
            'index' => $this->responseService->read(
                $context,
                $this->resourcePath(),
                'index',
                $this->title(),
                $this->vendorCrudService->index(),
            ),
            'show', 'edit' => $this->responseService->read(
                $context,
                $this->resourcePath(),
                $this->operation(),
                $this->title(),
                $this->resolveVendor($context),
            ),
            'new' => $this->responseService->read(
                $context,
                $this->resourcePath(),
                'new',
                $this->title(),
                [
                    'brandName' => '',
                    'ownerUserId' => null,
                ],
            ),
            default => $this->blocked($context),
        };
    }

    public function post(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return match ($this->operation()) {
            'create' => $this->createVendor($context),
            'update' => $this->updateVendor($context),
            default => $this->blocked($context),
        };
    }

    public function put(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return 'update' === $this->operation()
            ? $this->updateVendor($context)
            : $this->blocked($context);
    }

    public function patch(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return 'update' === $this->operation()
            ? $this->updateVendor($context)
            : $this->blocked($context);
    }

    public function delete(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->blocked($context);
    }

    abstract protected function resourcePath(): string;

    abstract protected function operation(): string;

    protected function title(): string
    {
        return 'Vendor '.str_replace('/', ' ', $this->resourcePath()).' '.$this->operation();
    }

    private function createVendor(CrudEntrypointContext $context): CrudSurfaceContract
    {
        $input = $context->request->request->all();
        $vendor = $this->vendorCrudService->create(new VendorCreateDTO(
            brandName: $this->stringValue($input['brandName'] ?? null),
            ownerUserId: $this->nullableIntValue($input['ownerUserId'] ?? null),
        ));

        return $this->responseService->mutation(
            $context,
            $this->resourcePath(),
            'create',
            $this->title(),
            $vendor,
        );
    }

    private function updateVendor(CrudEntrypointContext $context): CrudSurfaceContract
    {
        $input = $context->request->request->all();
        $vendor = $this->resolveVendor($context);
        $updated = $this->vendorCrudService->update($vendor, new VendorUpdateDTO(
            brandName: $this->nullableStringValue($input['brandName'] ?? null),
            status: $this->nullableStringValue($input['status'] ?? null),
            ownerUserId: $this->nullableIntValue($input['ownerUserId'] ?? null),
        ));

        return $this->responseService->mutation(
            $context,
            $this->resourcePath(),
            'update',
            $this->title(),
            $updated,
        );
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

        $vendor = $this->vendorCrudService->find($identifier);
        if (!$vendor instanceof VendorEntity) {
            throw new NotFoundHttpException('vendor_not_found');
        }

        return $vendor;
    }

    private function blocked(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->responseService->blocked(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
        );
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }

    private function nullableIntValue(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return false !== filter_var($value, FILTER_VALIDATE_INT) ? (int) $value : null;
    }
}
