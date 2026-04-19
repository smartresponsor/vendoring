<?php

declare(strict_types=1);

namespace App\Vendoring;

use App\Vendoring\DependencyInjection\VendoringExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony bundle facade for the Vendoring RC component.
 *
 * The component remains responsible for its own business surface.
 * The host application only enables this bundle and imports routes when needed.
 */
final class VendoringBundle extends Bundle
{
    protected function createContainerExtension(): VendoringExtension
    {
        return new VendoringExtension();
    }
}
