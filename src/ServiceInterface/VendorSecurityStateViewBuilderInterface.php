<?php

declare(strict_types=1);

namespace App\ServiceInterface;

use App\EntityInterface\VendorSecurityInterface;
use App\Projection\VendorSecurityStateView;

interface VendorSecurityStateViewBuilderInterface
{
    public function build(VendorSecurityInterface $security): VendorSecurityStateView;
}
