<?php
declare(strict_types = 1);

namespace App\Infrastructure\Repository\Vendor\Repository;

use App\RepositoryInterface\Vendor\Repository\DoctrineVendorRepositoryInterface;
use SmartResponsor\Vendor\Port\Repository\VendorRepositoryPort;
use SmartResponsor\Vendor\Entity\Vendor\Vendor;
use App\ValueObject\Vendor\VendorId;

final class DoctrineVendorRepository implements VendorRepositoryPort, DoctrineVendorRepositoryInterface
{
    public function __construct(/* EntityManagerInterface $em */)
    {
    }

    public function get(VendorId $id): ?Vendor
    {
        return null; /* stub */
    }

    public function save(Vendor $vendor): void
    {/* stub */
    }
}
