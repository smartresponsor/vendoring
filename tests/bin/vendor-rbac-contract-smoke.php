<?php

declare(strict_types=1);

use App\Vendoring\Service\Security\VendorAuthorizationMatrix;
use App\Vendoring\Service\Security\VendorAccessResolver;
use App\Vendoring\ValueObject\VendorRole;
use App\Vendoring\Entity\VendorUserAssignment;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$matrix = new VendorAuthorizationMatrix();

if (!$matrix->can(VendorRole::OWNER, 'ownership.write')) {
    throw new RuntimeException('RBAC smoke expected owner to grant ownership.write.');
}

if ($matrix->can(VendorRole::VIEWER, 'payouts.write')) {
    throw new RuntimeException('RBAC smoke expected viewer to remain read-only.');
}

$repository = new class implements VendorUserAssignmentRepositoryInterface {
    public function save(\App\Vendoring\EntityInterface\VendorUserAssignmentInterface $assignment, bool $flush = false): void {}
    public function remove(\App\Vendoring\EntityInterface\VendorUserAssignmentInterface $assignment, bool $flush = false): void {}
    public function findPrimaryForVendorId(int $vendorId): ?\App\Vendoring\EntityInterface\VendorUserAssignmentInterface
    {
        return null;
    }
    public function findActiveByVendorId(int $vendorId): array
    {
        return [new VendorUserAssignment($vendorId, 7, 'finance')];
    }
    public function findActiveByUserId(int $userId): array
    {
        return [];
    }
    public function findOneByVendorIdAndUserId(int $vendorId, int $userId): ?\App\Vendoring\EntityInterface\VendorUserAssignmentInterface
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
        return VendorUserAssignment::class;
    }
};

$resolver = new VendorAccessResolver($repository, $matrix);
$explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'payouts.write');

if (!$explanation['granted'] || 'role_grants_capability' !== $explanation['reason']) {
    throw new RuntimeException('RBAC smoke expected finance role to grant payouts.write.');
}

echo "vendor RBAC contract smoke passed\n";
