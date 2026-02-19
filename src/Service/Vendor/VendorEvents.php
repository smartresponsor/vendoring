<?php
declare(strict_types = 1);

namespace App\Service\Vendor;

use SmartResponsor\Vendor\Port\Event\EventPort;
use App\ServiceInterface\Vendor\VendorEventsInterface;

final class VendorEvents implements VendorEventsInterface
{
    public function __construct(private EventPort $bus)
    {
    }

    public function registered(string $id, string $name): void
    {
        $this->bus->publish('VendorRegistered', ['id' => $id, 'name' => $name], 'vendor-registered-' . $id);
    }
}
