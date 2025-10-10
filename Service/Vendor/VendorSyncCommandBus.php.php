<?php
declare(strict_types=1);

namespace App\CommandBus\Vendor;

interface VendorSyncCommandBus
{
    public function dispatch(object $command): void;
}
