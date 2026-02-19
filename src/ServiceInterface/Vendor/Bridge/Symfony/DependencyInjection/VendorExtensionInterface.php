<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Bridge\Symfony\DependencyInjection;

interface VendorExtensionInterface
{

    public function load(array $configs, ContainerBuilder $container): void;
}
