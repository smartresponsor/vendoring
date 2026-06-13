<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Cruding\Dto\Surface\CrudSurfaceRequest;
use App\Cruding\ServiceInterface\Surface\CrudSurfaceProviderInterface;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;

readonly class ApiPayoutStatementDetailSurfaceProvider implements CrudSurfaceProviderInterface
{
    public function __construct(
        private VendorStatementServiceInterface $statementService,
        private VendorStatementRequestResolverServiceInterface $requestResolver,
        private VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
    ) {
    }

    public function provide(CrudSurfaceRequest $request): CrudSurfaceContract
    {
        $httpRequest = $request->httpRequest;
        if (null === $httpRequest) {
            return $this->errorContract($request, 'statement_request_missing', 'Statement request context is unavailable.');
        }

        try {
            $this->statementWindowQueryRequestResolver->resolve($httpRequest);
        } catch (VendorApiQueryValidationException $exception) {
            return $this->errorContract($request, $exception->errorCode(), $exception->hint(), 422);
        }

        $vendorId = $this->scalarValue($request->routeContext->identifierValue());
        if (null === $vendorId) {
            return $this->errorContract($request, 'statement_vendor_required', 'Provide a vendor identifier in the route.', 422);
        }

        $dto = $this->requestResolver->resolveStatementRequest((string) $vendorId, $httpRequest);
        if (null === $dto) {
            return $this->errorContract(
                $request,
                'statement_params_required',
                'Provide tenantId, from, and to query parameters.',
                422,
            );
        }

        $statement = $this->statementService->build($dto);

        return CrudSurfaceContract::forSurface(
            'detail',
            $request->routeContext->toArray(),
            [
                'body' => [
                    [
                        'key' => 'statement',
                        'type' => 'statement',
                        'data' => $statement,
                        'meta' => [
                            'vendorId' => $dto->vendorId,
                            'tenantId' => $dto->tenantId,
                            'from' => $dto->from,
                            'to' => $dto->to,
                            'currency' => $dto->currency,
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Vendor statement',
                'format' => 'json',
                'status_code' => 200,
                'statement' => $statement,
                'request' => [
                    'vendorId' => $dto->vendorId,
                    'tenantId' => $dto->tenantId,
                    'from' => $dto->from,
                    'to' => $dto->to,
                    'currency' => $dto->currency,
                ],
            ],
        );
    }

    private function errorContract(CrudSurfaceRequest $request, string $errorCode, string $hint, int $statusCode = 422): CrudSurfaceContract
    {
        return CrudSurfaceContract::forSurface(
            'detail',
            $request->routeContext->toArray(),
            [
                'body' => [
                    [
                        'key' => 'validation',
                        'type' => 'notice',
                        'data' => [
                            'error' => $errorCode,
                            'hint' => $hint,
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Vendor statement',
                'format' => 'json',
                'status_code' => $statusCode,
                'error' => $errorCode,
                'hint' => $hint,
            ],
        );
    }

    private function scalarValue(mixed $value): string|int|null
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && '' !== trim($value)) {
            return ctype_digit($value) ? (int) $value : $value;
        }

        return null;
    }
}
