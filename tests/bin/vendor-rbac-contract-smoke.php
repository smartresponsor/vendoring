<?php

declare(strict_types=1);

use App\Vendoring\Service\Security\VendorAuthorizationMatrixService;
use App\Vendoring\Service\Security\VendorAccessResolverService;
use App\Vendoring\ValueObject\VendorRoleValueObject;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$matrix = new VendorAuthorizationMatrixService();

if (!$matrix->can(VendorRoleValueObject::OWNER, 'ownership.write')) {
    throw new RuntimeException('RBAC smoke expected owner to grant ownership.write.');
}

if ($matrix->can(VendorRoleValueObject::VIEWER, 'payouts.write')) {
    throw new RuntimeException('RBAC smoke expected viewer to remain read-only.');
}

$repository = new class implements VendorUserAssignmentRepositoryInterface {
    public function save(\App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void {}
    public function remove(\App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface $assignment, bool $flush = false): void {}
    public function findPrimaryForVendorId(int $vendorId): ?\App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface
    {
        return null;
    }
    public function findActiveByVendorId(int $vendorId): array
    {
        return [new VendorUserAssignmentEntity($vendorId, 7, 'finance')];
    }
    public function findActiveByUserId(int $userId): array
    {
        return [];
    }
    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?\App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface
    {
        return null;
    }
    public function find(mixed $id, mixed $lockMode = null, mixed $lockVersion = null): ?object
    {
        return null;
    }
    public function findAll(): array
    {
        return [];
    }
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }
    public function findOneBy(array $criteria): ?object
    {
        return null;
    }
    public function getClassName(): string
    {
        return VendorUserAssignmentEntity::class;
    }
};

$resolver = new VendorAccessResolverService($repository, $matrix);
$explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'payouts.write');

if (!$explanation['granted'] || 'role_grants_capability' !== $explanation['reason']) {
    throw new RuntimeException('RBAC smoke expected finance role to grant payouts.write.');
}

echo "vendor RBAC contract smoke passed\n";
