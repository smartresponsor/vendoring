<?php

declare(strict_types=1);

namespace App\Controller;

use App\RepositoryInterface\VendorRepositoryInterface;
use App\Service\VendorProfileRequestResolver;
use App\ServiceInterface\VendorProfileRequestResolverInterface;
use App\ServiceInterface\VendorProfileServiceInterface;
use App\ServiceInterface\VendorProfileViewBuilderInterface;
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
    private readonly VendorProfileViewBuilderInterface $profileViewBuilder;
    private readonly VendorProfileRequestResolverInterface $profileRequestResolver;

    public function __construct(
        VendorRepositoryInterface $vendorRepository,
        VendorProfileServiceInterface $profileService,
        VendorProfileViewBuilderInterface $profileViewBuilder,
        ?VendorProfileRequestResolverInterface $profileRequestResolver = null,
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->profileService = $profileService;
        $this->profileViewBuilder = $profileViewBuilder;
        $this->profileRequestResolver = $profileRequestResolver ?? new VendorProfileRequestResolver();
    }

    #[Route('/vendor/{vendorId}', methods: ['PATCH'])]
    public function update(int $vendorId, Request $request): JsonResponse
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        try {
            $payload = $request->toArray();
            $this->profileService->upsert($vendor, $this->profileRequestResolver->resolve($vendorId, $payload));
        } catch (JsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (OptimisticLockException|ORMException) {
            return new JsonResponse(['error' => 'profile_update_conflict'], 409);
        }

        $view = $this->profileViewBuilder->buildForVendorId($vendorId);

        if (null === $view) {
            return new JsonResponse(['error' => 'profile_view_unavailable'], 500);
        }

        return new JsonResponse(['data' => $view->toArray()], 200);
    }

    #[Route('/vendor/{vendorId}', methods: ['GET'])]
    public function show(int $vendorId): JsonResponse
    {
        $view = $this->profileViewBuilder->buildForVendorId($vendorId);

        if (null === $view) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $view->toArray()], 200);
    }

}
