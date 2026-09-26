<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Runtime;

interface VendorAppEnvResolverInterface
{
    public function resolve(): string;
}
