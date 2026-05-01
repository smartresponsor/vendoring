<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Security;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
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
    public function createKey(VendorEntity $vendor, string $permissions): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);

        $key = new VendorApiKeyEntity($vendor, $tokenHash, $permissions);

        $this->apiKeyRepo->save($key, true);

        return $plainToken;
    }

    /** @throws RandomException */
    public function rotateKey(VendorApiKeyEntity $existingKey): string
    {
        $existingKey->deactivate();

        $newToken = bin2hex(random_bytes(32));
        $newHash = hash('sha256', $newToken);

        $newKey = new VendorApiKeyEntity(
            $existingKey->getVendor(),
            $newHash,
            $existingKey->getPermissions(),
        );

        $this->apiKeyRepo->save($existingKey);
        $this->apiKeyRepo->save($newKey);
        $this->em->flush();

        return $newToken;
    }

    public function revokeKey(VendorApiKeyEntity $apiKey): void
    {
        $apiKey->deactivate();

        $this->apiKeyRepo->save($apiKey, true);
    }

    public function validateToken(string $plainToken, ?string $permission = null): ?VendorEntity
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
    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?VendorEntity
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
    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?VendorEntity
    {
        return $this->validateAuthorizationHeader($authorizationHeader);
    }
}
