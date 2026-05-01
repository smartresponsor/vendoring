<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileProjectionBuilderServiceInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vendor-profile')]
final class VendorProfileController extends AbstractController
{
    private readonly VendorRepositoryInterface $vendorRepository;
    private readonly VendorProfileServiceInterface $profileService;
    private readonly VendorProfileProjectionBuilderServiceInterface $profileProjectionBuilder;
    private readonly VendorProfileRequestResolverServiceInterface $profileRequestResolver;

    public function __construct(
        VendorRepositoryInterface $vendorRepository,
        VendorProfileServiceInterface $profileService,
        VendorProfileProjectionBuilderServiceInterface $profileProjectionBuilder,
        VendorProfileRequestResolverServiceInterface $profileRequestResolver,
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->profileService = $profileService;
        $this->profileProjectionBuilder = $profileProjectionBuilder;
        $this->profileRequestResolver = $profileRequestResolver;
    }

    #[Route('/vendor/{vendorId}', methods: ['PATCH'])]
    public function update(int $vendorId, Request $request): JsonResponse
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->toArray();
            $this->profileService->upsert($vendor, $this->profileRequestResolver->resolve($vendorId, $payload));
        } catch (JsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (OptimisticLockException|ORMException) {
            return new JsonResponse(['error' => 'profile_update_conflict'], 409);
        }

        $projection = $this->profileProjectionBuilder->buildForVendorId($vendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'profile_projection_unavailable'], 500);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    #[Route('/vendor/{vendorId}', methods: ['GET'])]
    public function show(int $vendorId): JsonResponse
    {
        $projection = $this->profileProjectionBuilder->buildForVendorId($vendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

}
