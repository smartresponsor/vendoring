<?php
declare(strict_types = 1);

namespace App\ServiceInterface\Vendor;

interface VendorSyncCommandBusInterface
{
    public function dispatch(object $command): void;
}
