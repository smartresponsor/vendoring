<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Runtime;

interface VendorAppEnvResolverServiceInterface
{
    public function resolve(): string;
}
