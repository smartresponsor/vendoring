<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\Vendor;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<Vendor>
 */
interface VendorRepositoryInterface extends ObjectRepository {}
