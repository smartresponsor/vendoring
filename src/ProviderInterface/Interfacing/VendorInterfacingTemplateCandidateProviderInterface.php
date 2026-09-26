<?php

declare(strict_types=1);

namespace App\Vendoring\ProviderInterface\Interfacing;

/**
 * Provides ordered Interfacing template candidates for a prepared Vendoring surface.
 */
interface VendorInterfacingTemplateCandidateProviderInterface
{
    /**
     * @return list<string>
     */
    public function candidatesFor(string $surfaceName): array;
}
