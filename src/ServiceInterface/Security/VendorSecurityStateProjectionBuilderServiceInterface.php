<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Security;

use App\Vendoring\EntityInterface\Vendor\VendorSecurityEntityInterface;
use App\Vendoring\Projection\Vendor\VendorSecurityStateProjection;

interface VendorSecurityStateProjectionBuilderServiceInterface
{
    public function build(VendorSecurityEntityInterface $security): VendorSecurityStateProjection;
}
