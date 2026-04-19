<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\EntityInterface\VendorSecurityInterface;
use App\Vendoring\Projection\VendorSecurityStateView;

interface VendorSecurityStateViewBuilderInterface
{
    public function build(VendorSecurityInterface $security): VendorSecurityStateView;
}
