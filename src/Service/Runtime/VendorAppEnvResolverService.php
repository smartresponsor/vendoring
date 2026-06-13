<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Runtime;

use App\Vendoring\ServiceInterface\Runtime\VendorAppEnvResolverServiceInterface;

final class VendorAppEnvResolverService implements VendorAppEnvResolverServiceInterface
{
    public function resolve(): string
    {
        $serverAppEnv = $_SERVER['APP_ENV'] ?? null;
        if (is_string($serverAppEnv) && '' !== trim($serverAppEnv)) {
            return $serverAppEnv;
        }

        $envAppEnv = $_ENV['APP_ENV'] ?? null;
        if (is_string($envAppEnv) && '' !== trim($envAppEnv)) {
            return $envAppEnv;
        }

        return 'dev';
    }
}
