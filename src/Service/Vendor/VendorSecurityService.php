<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorApiKey;
use App\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use App\ServiceInterface\Vendor\VendorSecurityServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final class VendorSecurityService implements VendorSecurityServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface          $em,
        private readonly VendorApiKeyRepositoryInterface $apiKeyRepo,
    )
    {
    }

    public function createKey(Vendor $vendor, string $permissions): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $key = new VendorApiKey($vendor, $tokenHash, $permissions);

        $this->apiKeyRepo->save($key, true);

        return $plainToken;
    }

    public function rotateKey(VendorApiKey $existingKey): string
    {
        $existingKey->deactivate();

        $newToken = bin2hex(random_bytes(32));
        $newHash = hash('sha256', $newToken);

        $newKey = new VendorApiKey(
            $existingKey->getVendor(),
            $newHash,
            $existingKey->getPermissions(),
        );

        $this->apiKeyRepo->save($existingKey, false);
        $this->apiKeyRepo->save($newKey, false);
        $this->em->flush();

        return $newToken;
    }

    public function revokeKey(VendorApiKey $apiKey): void
    {
        $apiKey->deactivate();

        $this->apiKeyRepo->save($apiKey, true);
    }

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor
    {
        $tokenHash = hash('sha256', $plainToken);

        $apiKey = $this->apiKeyRepo->findActiveByToken($tokenHash);

        if (null === $apiKey) {
            return null;
        }

        if (null !== $permission && !$apiKey->hasPermission($permission)) {
            return null;
        }

        $apiKey->touch();
        $this->em->flush();

        return $apiKey->getVendor();
    }

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor
    {
        $authorizationHeader = trim($authorizationHeader);

        if ('' === $authorizationHeader) {
            return null;
        }

        if (0 === stripos($authorizationHeader, 'bearer ')) {
            $plainToken = trim(substr($authorizationHeader, 7));
        } else {
            $plainToken = $authorizationHeader;
        }

        if ('' === $plainToken) {
            return null;
        }

        return $this->validateToken($plainToken);
    }
}
