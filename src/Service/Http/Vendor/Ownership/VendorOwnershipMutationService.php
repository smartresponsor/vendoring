<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Ownership;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteServiceInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorOwnershipMutationService
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorOwnershipWriteRequestResolverServiceInterface $requestResolver,
        private readonly VendorOwnershipWriteServiceInterface $writeService,
        private readonly VendorOwnershipProjectionBuilderServiceInterface $projectionBuilder,
    ) {
    }

    public function __invoke(object $request): JsonResponse
    {
        if (!$request instanceof Request) {
            return new JsonResponse(['error' => 'unsupported_request'], 400);
        }

        $vendorId = $this->resolveVendorId($request);
        if (null === $vendorId) {
            return new JsonResponse(['error' => 'vendor_id_required'], 422);
        }

        $mutation = $this->resolveMutation($request);
        if (null === $mutation) {
            return new JsonResponse([
                'error' => 'vendor_ownership_mutation_required',
                'supportedMutations' => array_keys($this->mutationHandlerMap()),
            ], 422);
        }

        return match ($mutation) {
            'payment' => $this->upsertPayment($vendorId, $request),
            'commission' => $this->upsertCommission($vendorId, $request),
            'conversation' => $this->createConversation($vendorId, $request),
            'shipment' => $this->upsertShipment($vendorId, $request),
            'group' => $this->upsertGroup($vendorId, $request),
            'category' => $this->upsertCategory($vendorId, $request),
            'favourite' => $this->upsertFavourite($vendorId, $request),
            'wishlist' => $this->upsertWishlist($vendorId, $request),
            'code_storage' => $this->upsertCodeStorage($vendorId, $request),
            'remember_me_token' => $this->upsertRememberMeToken($vendorId, $request),
            'customer_order' => $this->upsertCustomerOrder($vendorId, $request),
            default => new JsonResponse([
                'error' => 'unsupported_vendor_ownership_mutation',
                'mutation' => $mutation,
                'supportedMutations' => array_keys($this->mutationHandlerMap()),
            ], 422),
        };
    }

    public function upsertPayment(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertPayment($vendor, $this->requestResolver->resolvePayment($vendorId, $payload));
        });
    }

    public function upsertCommission(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCommission($vendor, $this->requestResolver->resolveCommission($vendorId, $payload));
        });
    }

    public function createConversation(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->createConversation($vendor, $this->requestResolver->resolveConversation($vendorId, $payload));
        });
    }

    public function upsertShipment(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertShipment($vendor, $this->requestResolver->resolveShipment($vendorId, $payload));
        });
    }

    public function upsertGroup(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertGroup($vendor, $this->requestResolver->resolveGroup($vendorId, $payload));
        });
    }

    public function upsertCategory(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCategory($vendor, $this->requestResolver->resolveCategory($vendorId, $payload));
        });
    }

    public function upsertFavourite(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertFavourite($vendor, $this->requestResolver->resolveFavourite($vendorId, $payload));
        });
    }

    public function upsertWishlist(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertWishlist($vendor, $this->requestResolver->resolveWishlist($vendorId, $payload));
        });
    }

    public function upsertCodeStorage(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCodeStorage($vendor, $this->requestResolver->resolveCodeStorage($vendorId, $payload));
        });
    }

    public function upsertRememberMeToken(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertRememberMeToken($vendor, $this->requestResolver->resolveRememberMeToken($vendorId, $payload));
        });
    }

    public function upsertCustomerOrder(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (VendorEntity $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCustomerOrder($vendor, $this->requestResolver->resolveCustomerOrder($vendorId, $payload));
        });
    }

    private function resolveVendorId(Request $request): ?int
    {
        foreach (['vendorId', 'id', 'slug', 'item'] as $field) {
            $value = $request->attributes->get($field);
            if (is_scalar($value) && ctype_digit((string) $value)) {
                return (int) $value;
            }

            $value = $request->query->get($field);
            if (is_scalar($value) && ctype_digit((string) $value)) {
                return (int) $value;
            }

            $value = $request->request->get($field);
            if (is_scalar($value) && ctype_digit((string) $value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function resolveMutation(Request $request): ?string
    {
        foreach (['mutation', 'mutationType', 'ownershipMutation', 'action', 'type'] as $field) {
            $value = $request->attributes->get($field);
            if (is_scalar($value)) {
                return $this->normalizeMutation((string) $value);
            }

            $value = $request->query->get($field);
            if (is_scalar($value)) {
                return $this->normalizeMutation((string) $value);
            }

            $value = $request->request->get($field);
            if (is_scalar($value)) {
                return $this->normalizeMutation((string) $value);
            }
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return null;
        }

        foreach (['mutation', 'mutationType', 'ownershipMutation', 'action', 'type'] as $field) {
            $value = $payload[$field] ?? null;
            if (is_scalar($value)) {
                return $this->normalizeMutation((string) $value);
            }
        }

        return null;
    }

    private function normalizeMutation(string $mutation): ?string
    {
        $normalized = strtolower(trim($mutation));
        if ('' === $normalized) {
            return null;
        }

        $normalized = str_replace(['-', '.', ' '], '_', $normalized);

        return $this->mutationHandlerMap()[$normalized] ?? $normalized;
    }

    /** @return array<string, string> */
    private function mutationHandlerMap(): array
    {
        return [
            'payment' => 'payment',
            'upsert_payment' => 'payment',
            'commission' => 'commission',
            'upsert_commission' => 'commission',
            'conversation' => 'conversation',
            'create_conversation' => 'conversation',
            'shipment' => 'shipment',
            'upsert_shipment' => 'shipment',
            'group' => 'group',
            'upsert_group' => 'group',
            'category' => 'category',
            'upsert_category' => 'category',
            'favourite' => 'favourite',
            'favorite' => 'favourite',
            'upsert_favourite' => 'favourite',
            'upsert_favorite' => 'favourite',
            'wishlist' => 'wishlist',
            'upsert_wishlist' => 'wishlist',
            'code_storage' => 'code_storage',
            'codestorage' => 'code_storage',
            'upsert_code_storage' => 'code_storage',
            'remember_me_token' => 'remember_me_token',
            'remembermetoken' => 'remember_me_token',
            'upsert_remember_me_token' => 'remember_me_token',
            'customer_order' => 'customer_order',
            'customerorder' => 'customer_order',
            'upsert_customer_order' => 'customer_order',
        ];
    }

    /** @param callable(VendorEntity, array<string,mixed>): void $handler */
    private function mutate(int $vendorId, Request $request, callable $handler): JsonResponse
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            $handler($vendor, $payload);
        } catch (JsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (OptimisticLockException|ORMException) {
            return new JsonResponse(['error' => 'vendor_ownership_write_conflict'], 409);
        }

        $projection = $this->projectionBuilder->buildForVendorId($vendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'vendor_ownership_projection_unavailable'], 500);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}
