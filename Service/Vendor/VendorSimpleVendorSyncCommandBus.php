<?php
declare(strict_types=1);

namespace App\CommandBus\Vendor;

use Psr\Container\ContainerInterface;

final class SimpleVendorSyncCommandBus implements VendorSyncCommandBus
{
    public function __construct(private readonly ContainerInterface $container) {}

    public function dispatch(object $command): void
    {
        $class = $command::class . 'Handler';
        if (!$this->container->has($class)) {
            throw new \RuntimeException('Handler not found for ' . $class);
        }
        $handler = $this->container->get($class);
        $handler($command);
    }
}
