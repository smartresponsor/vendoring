<?php
declare(strict_types=1);

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorApiKey;
use App\Repository\Vendor\VendorApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;

final class VendorSecurityService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorApiKeyRepository $repo
    ) {}

    /** @return array{plain:string,key:VendorApiKey} */
    public function createKey(Vendor $vendor, array $permissions = [], ?\DateTimeImmutable $expiresAt = null): array
    {
        $plain = bin2hex(random_bytes(24));
        $hash = hash('sha256', $plain);
        $key = new VendorApiKey($vendor, $hash, $permissions, $expiresAt);
        $this->em->persist($key);
        $this->em->flush();
        return ['plain' => $plain, 'key' => $key];
    }

    public function rotateKey(VendorApiKey $key): string
    {
        $plain = bin2hex(random_bytes(24));
        $hash = hash('sha256', $plain);
        $ref = new \ReflectionProperty($key, 'token');
        $ref->setAccessible(true);
        $ref->setValue($key, $hash);
        $this->em->flush();
        return $plain;
    }

    public function revoke(VendorApiKey $key): void
    {
        $key->revoke();
        $this->em->flush();
    }

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor
    {
        $hash = hash('sha256', $plainToken);
        $key = $this->repo->findActiveByToken($hash);
        if (!$key || !$key->isActive()) return null;

        if ($permission && !in_array($permission, $key->getPermissions(), true)) {
            return null;
        }
        $key->markUsed();
        $this->em->flush();
        return $key->getVendor();
    }
}
