<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\RepositoryInterface\VendorApiKeyRepositoryInterface;
use App\Vendoring\ServiceInterface\VendorApiKeyServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ManagerException;
use Random\RandomException;

/**
 * Canonical machine-access service for vendor API keys.
 *
 * This service owns API-key issuance, rotation, revocation and bearer-token
 * vendor resolution. It does not model human credentials or external User
 * identity, which remain outside Vendoring.
 */
final readonly class VendorApiKeyService implements VendorApiKeyServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorApiKeyRepositoryInterface $apiKeyRepo,
    ) {}

    /** @throws RandomException */
    public function createKey(Vendor $vendor, string $permissions): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $key = new VendorApiKey($vendor, $tokenHash, $permissions);

        $this->apiKeyRepo->save($key, true);

        return $plainToken;
    }

    /** @throws RandomException */
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

        $this->apiKeyRepo->save($existingKey);
        $this->apiKeyRepo->save($newKey);
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

    /** @throws ManagerException */
    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor
    {
        $authorizationHeader = trim($authorizationHeader);

        if ('' === $authorizationHeader) {
            return null;
        }

        $plainToken = 0 === stripos($authorizationHeader, 'bearer ')
            ? trim(substr($authorizationHeader, 7))
            : $authorizationHeader;

        if ('' === $plainToken) {
            return null;
        }

        return $this->validateToken($plainToken, $permission);
    }

    /** @throws ManagerException */
    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor
    {
        return $this->validateAuthorizationHeader($authorizationHeader);
    }
}
