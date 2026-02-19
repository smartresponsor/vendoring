<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor\Port\Service;

use App\Entity\Vendor\Vendor;
use App\ValueObject\Vendor\VendorId;

interface VendorServicePort
{
    public function register(Vendor $vendor): void;

    public function rename(VendorId $id, string $newName): void;
}
