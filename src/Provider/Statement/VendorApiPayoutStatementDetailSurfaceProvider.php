<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Statement;

use App\Cruding\DTO\Resource\CrudResourceRequestDTO;
use App\Cruding\ServiceInterface\Resource\CrudResourceProviderInterface;
use App\Cruding\ValueObject\Resource\CrudResourceContract;
use App\Vendoring\Exception\Api\VendorApiQueryValidationException;
use App\Vendoring\ResolverInterface\Api\VendorStatementWindowQueryRequestResolverInterface;
use App\Vendoring\ResolverInterface\Statement\VendorStatementRequestResolverInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;

readonly class VendorApiPayoutStatementDetailSurfaceProvider implements CrudResourceProviderInterface
{
    public function __construct(
        private VendorStatementServiceInterface $statementService,
        private VendorStatementRequestResolverInterface $requestResolver,
        private VendorStatementWindowQueryRequestResolverInterface $statementWindowQueryRequestResolver,
    ) {
    }

    public function provide(CrudResourceRequestDTO $request): CrudResourceContract
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
                'Provide from and to query parameters.',
                422,
            );
        }

        $statement = $this->statementService->build($dto);

        return CrudResourceContract::forResource(
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
                    'from' => $dto->from,
                    'to' => $dto->to,
                    'currency' => $dto->currency,
                ],
            ],
        );
    }

    private function errorContract(CrudResourceRequestDTO $request, string $errorCode, string $hint, int $statusCode = 422): CrudResourceContract
    {
        return CrudResourceContract::forResource(
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
