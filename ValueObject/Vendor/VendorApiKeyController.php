<?php
declare(strict_types=1);

namespace App\Controller\Vendor;

use App\DTO\Vendor\VendorCreateDTO;
use App\Repository\Vendor\VendorRepository;
use App\Repository\Vendor\VendorApiKeyRepository;
use App\Service\Vendor\VendorSecurityService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class VendorApiKeyController
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly VendorApiKeyRepository $keys,
        private readonly VendorSecurityService $security
    ) {}

    #[Route('/api/vendor/api-key', name: 'api_vendor_api_key_create', methods: ['POST'])]
    public function create(Request $req): JsonResponse
    {
        $vendorId = (int) ($req->get('vendorId') ?? $req->attributes->get('vendorId') ?? 0);
        $permissions = (array) ($req->get('permissions') ?? ['vendor:read']);
        $vendor = $this->vendors->find($vendorId);
        if (!$vendor) return new JsonResponse(['error' => 'Vendor not found'], 404);

        $expiresAt = null;
        if ($e = $req->get('expiresAt')) {
            $expiresAt = new \DateTimeImmutable($e);
        }

        $result = $this->security->createKey($vendor, $permissions, $expiresAt);
        return new JsonResponse([
            'vendorId' => $vendorId,
            'token' => $result['plain'],
            'permissions' => $permissions,
            'expiresAt' => $expiresAt?->format(DATE_ATOM),
        ], 201);
    }

    #[Route('/api/vendor/api-keys', name: 'api_vendor_api_keys_list', methods: ['GET'])]
    public function list(Request $req): JsonResponse
    {
        $vendorId = (int) ($req->get('vendorId') ?? $req->attributes->get('vendorId') ?? 0);
        $vendor = $this->vendors->find($vendorId);
        if (!$vendor) return new JsonResponse(['error' => 'Vendor not found'], 404);
        $keys = $this->keys->findBy(['vendor' => $vendor]);
        return new JsonResponse(array_map(function($k) {
            return [
                'id' => $k->getId(),
                'permissions' => $k->getPermissions(),
                'status' => $k->getStatus(),
                'lastUsedAt' => $k->getLastUsedAt()?->format(DATE_ATOM),
            ];
        }, $keys));
    }

    #[Route('/api/vendor/api-key/{id}', name: 'api_vendor_api_key_revoke', methods: ['DELETE'])]
    public function revoke(int $id): JsonResponse
    {
        $key = $this->keys->find($id);
        if (!$key) return new JsonResponse(['error' => 'Key not found'], 404);
        $this->security->revoke($key);
        return new JsonResponse(['ok' => true]);
    }
}
