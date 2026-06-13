<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Interfacing;

/**
 * Provides ordered Interfacing template candidates for a prepared Vendoring surface.
 */
interface VendorInterfacingTemplateCandidateProviderServiceInterface
{
    /**
     * @return list<string>
     */
    public function candidatesFor(string $surfaceName): array;
}
