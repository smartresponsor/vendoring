<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\VendorProfileDTO;
use App\RepositoryInterface\VendorRepositoryInterface;
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
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorProfileServiceInterface $profileService,
        private readonly VendorProfileViewBuilderInterface $profileViewBuilder,
    ) {}

    #[Route('/vendor/{vendorId}', methods: ['PATCH'])]
    public function update(int $vendorId, Request $request): JsonResponse
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        try {
            $payload = $request->toArray();
            $this->profileService->upsert($vendor, $this->buildDto($vendorId, $payload));
        } catch (JsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (OptimisticLockException|ORMException $exception) {
            return new JsonResponse([
                'error' => 'vendor_profile_persist_failed',
                'detail' => $exception->getMessage(),
            ], 409);
        }

        return $this->buildVendorProfileResponse($vendorId);
    }

    #[Route('/vendor/{vendorId}', methods: ['GET'])]
    public function show(int $vendorId): JsonResponse
    {
        return $this->buildVendorProfileResponse($vendorId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildDto(int $vendorId, array $payload): VendorProfileDTO
    {
        $socials = $payload['socials'] ?? null;

        if (null !== $socials && !is_array($socials)) {
            throw new InvalidArgumentException('socials_must_be_object');
        }

        return new VendorProfileDTO(
            vendorId: $vendorId,
            displayName: $this->nullableString($payload, 'displayName'),
            about: $this->nullableString($payload, 'about'),
            website: $this->nullableString($payload, 'website'),
            socials: $this->normalizeSocials($socials),
            seoTitle: $this->nullableString($payload, 'seoTitle'),
            seoDescription: $this->nullableString($payload, 'seoDescription'),
            publicationAction: $this->nullableString($payload, 'publicationAction'),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function nullableString(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;

        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('%s_must_be_string', $field));
        }

        return (string) $value;
    }

    /**
     * @param array<string, string>|null $socials
     *
     * @return array<string, string>|null
     */
    private function normalizeSocials(?array $socials): ?array
    {
        if (null === $socials) {
            return null;
        }

        $normalized = [];

        foreach ($socials as $network => $url) {
            if (!is_scalar($url)) {
                throw new InvalidArgumentException('socials_must_be_object');
            }

            $normalized[(string) $network] = (string) $url;
        }

        return $normalized;
    }

    private function buildVendorProfileResponse(int $vendorId): JsonResponse
    {
        $view = $this->profileViewBuilder->buildForVendorId($vendorId);
        if (null === $view) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $view->toArray()], 200);
    }
}
