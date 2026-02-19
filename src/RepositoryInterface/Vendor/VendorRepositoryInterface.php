<?php
declare(strict_types=1);

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\Vendor;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Vendor>
 */
interface VendorRepositoryInterface extends ObjectRepository
{
}
