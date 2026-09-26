<?php

declare(strict_types=1);

namespace App\Vendoring\BuilderInterface\Security;

use App\Vendoring\EntityInterface\VendorSecurityEntityInterface;
use App\Vendoring\Projection\VendorSecurityStateProjection;

interface VendorSecurityStateProjectionBuilderInterface
{
    public function build(VendorSecurityEntityInterface $security): VendorSecurityStateProjection;
}
