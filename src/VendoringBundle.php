<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\VendoringExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle facade for the Vendoring RC component.
 *
 * The component remains responsible for its own business surface.
 * The host application only enables this bundle and imports routes when needed.
 */
final class VendoringBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new VendoringExtension();
    }
}
