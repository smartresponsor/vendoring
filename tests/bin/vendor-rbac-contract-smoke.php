<?php

declare(strict_types=1);

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\Service\Security\VendorAccessResolverService;
use App\Vendoring\Service\Security\VendorAuthorizationMatrixService;
use App\Vendoring\ValueObject\VendorRoleValueObject;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$matrix = new VendorAuthorizationMatrixService();

if (!$matrix->can(VendorRoleValueObject::OWNER, 'ownership.write')) {
    throw new RuntimeException('RBAC smoke expected owner to grant ownership.write.');
}

if ($matrix->can(VendorRoleValueObject::VIEWER, 'payouts.write')) {
    throw new RuntimeException('RBAC smoke expected viewer to remain read-only.');
}

$repository = new class implements VendorUserAssignmentRepositoryInterface {
    public function save(VendorUserAssignmentEntity $entity, bool $flush = false): void
    {
    }

    public function findActiveByVendorId(int $vendorId): array
    {
        return [new VendorUserAssignmentEntity($vendorId, 7, 'finance')];
    }

    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?VendorUserAssignmentEntity
    {
        return null;
    }

    public function find(mixed $id): ?VendorUserAssignmentEntity
    {
        return null;
    }
};

$resolver = new VendorAccessResolverService($repository, $matrix);
$explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'payouts.write');

if (!$explanation['granted'] || 'role_grants_capability' !== $explanation['reason']) {
    throw new RuntimeException('RBAC smoke expected finance role to grant payouts.write.');
}

echo "vendor RBAC contract smoke passed\n";
