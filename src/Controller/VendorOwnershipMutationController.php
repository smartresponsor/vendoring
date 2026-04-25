<?php

declare(strict_types=1);

namespace App\Vendoring\Controller;

use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteRequestResolverInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteServiceInterface;
use App\Vendoring\ServiceInterface\VendorOwnershipViewBuilderInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-ownership')]
final class VendorOwnershipMutationController extends AbstractController
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorOwnershipWriteRequestResolverInterface $requestResolver,
        private readonly VendorOwnershipWriteServiceInterface $writeService,
        private readonly VendorOwnershipViewBuilderInterface $viewBuilder,
    ) {}

    #[Route('/vendor/{vendorId}/payments', methods: ['POST'])]
    public function upsertPayment(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertPayment($vendor, $this->requestResolver->resolvePayment($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/commissions', methods: ['POST'])]
    public function upsertCommission(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCommission($vendor, $this->requestResolver->resolveCommission($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/conversations', methods: ['POST'])]
    public function createConversation(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->createConversation($vendor, $this->requestResolver->resolveConversation($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/shipments', methods: ['POST'])]
    public function upsertShipment(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertShipment($vendor, $this->requestResolver->resolveShipment($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/groups', methods: ['POST'])]
    public function upsertGroup(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertGroup($vendor, $this->requestResolver->resolveGroup($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/categories', methods: ['POST'])]
    public function upsertCategory(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCategory($vendor, $this->requestResolver->resolveCategory($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/favourites', methods: ['POST'])]
    public function upsertFavourite(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertFavourite($vendor, $this->requestResolver->resolveFavourite($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/wishlists', methods: ['POST'])]
    public function upsertWishlist(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertWishlist($vendor, $this->requestResolver->resolveWishlist($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/codes', methods: ['POST'])]
    public function upsertCodeStorage(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCodeStorage($vendor, $this->requestResolver->resolveCodeStorage($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/remember-me-tokens', methods: ['POST'])]
    public function upsertRememberMeToken(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertRememberMeToken($vendor, $this->requestResolver->resolveRememberMeToken($vendorId, $payload));
        });
    }

    #[Route('/vendor/{vendorId}/customer-orders', methods: ['POST'])]
    public function upsertCustomerOrder(int $vendorId, Request $request): JsonResponse
    {
        return $this->mutate($vendorId, $request, function (object $vendor, array $payload) use ($vendorId): void {
            $this->writeService->upsertCustomerOrder($vendor, $this->requestResolver->resolveCustomerOrder($vendorId, $payload));
        });
    }

    /** @param callable(object, array<string,mixed>): void $handler */
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
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (OptimisticLockException|ORMException) {
            return new JsonResponse(['error' => 'vendor_ownership_write_conflict'], 409);
        }

        $view = $this->viewBuilder->buildForVendorId($vendorId);

        if (null === $view) {
            return new JsonResponse(['error' => 'vendor_ownership_view_unavailable'], 500);
        }

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}
