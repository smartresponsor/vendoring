<?php

declare(strict_types=1);

use App\Vendoring\Form\Ops\VendorTransactionCreateType;
use App\Vendoring\Form\Ops\VendorTransactionStatusUpdateType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists('Symfony\\Component\\Form\\AbstractType')) {
        return;
    }

    $services = $configurator->services();
    $services->set(VendorTransactionCreateType::class)
        ->autowire()
        ->autoconfigure();
    $services->set(VendorTransactionStatusUpdateType::class)
        ->autowire()
        ->autoconfigure();
};
