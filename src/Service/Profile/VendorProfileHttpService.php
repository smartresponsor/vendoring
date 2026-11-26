<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\BuilderInterface\Profile\VendorProfileProjectionBuilderInterface;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\ResolverInterface\Profile\VendorProfileRequestResolverInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileServiceInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorProfileHttpService
{
    private readonly VendorRepositoryInterface $vendorRepository;
    private readonly VendorProfileServiceInterface $profileService;
    private readonly VendorProfileProjectionBuilderInterface $profileProjectionBuilder;
    private readonly VendorProfileRequestResolverInterface $profileRequestResolver;

    public function __construct(
        VendorRepositoryInterface $vendorRepository,
        VendorProfileServiceInterface $profileService,
        VendorProfileProjectionBuilderInterface $profileProjectionBuilder,
        VendorProfileRequestResolverInterface $profileRequestResolver,
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->profileService = $profileService;
        $this->profileProjectionBuilder = $profileProjectionBuilder;
        $this->profileRequestResolver = $profileRequestResolver;
    }

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
        } catch (\InvalidArgumentException $exception) {
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

    public function show(int $id): JsonResponse
    {
        $projection = $this->profileProjectionBuilder->buildForVendorId($id);

        if (null === $projection) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}
