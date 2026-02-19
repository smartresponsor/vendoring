<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Bridge\Symfony\DependencyInjection;

use App\Repository\Vendor\InMemoryVendorRepository;
use App\Repository\Vendor\PdoVendorRepository;
use App\ServiceInterface\Vendor\Port\Repository\VendorRepositoryPort;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class VendorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $adapter = $container->getParameter('env(VENDOR_REPOSITORY_ADAPTER)') ?? 'inmemory';

        if ($adapter === 'pdo') {
            $container->register('vendor.pdo', \PDO::class)->setArguments([
                '%vendor.dsn%',
            ]);
            $container->register('vendor.repo', PdoVendorRepository::class)
                ->setArguments([new Reference('vendor.pdo')]);
        } else {
            $container->register('vendor.repo', InMemoryVendorRepository::class);
        }

        // Alias the port to the adapter implementation
        $container->setAlias(VendorRepositoryPort::class, 'vendor.repo')->setPublic(true);
    }
}
