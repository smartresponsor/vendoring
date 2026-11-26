<?php

declare(strict_types=1);

namespace App\Vendoring\Security;

use App\Vendoring\Service\Transaction\VendorTransactionHttpService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Select only the explicit, independently ownership-checked JSON endpoints.
 * A path prefix alone must never divert a host human-authenticated route.
 */
final class VendorTransactionMachineRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request): bool
    {
        $controller = $request->attributes->get('_controller');
        $path = $request->getPathInfo();

        return match ($controller) {
            VendorTransactionHttpService::class.'::create' => $request->isMethod('POST') && '/api/vendor/transaction' === $path,
            VendorTransactionHttpService::class.'::listByVendor' => $request->isMethod('GET') && 1 === preg_match('~^/api/vendor/transaction/(?:list/)?[1-9][0-9]*$~D', $path),
            VendorTransactionHttpService::class.'::updateStatus' => $request->isMethod('POST') && 1 === preg_match('~^/api/vendor/transaction/status/[1-9][0-9]*/[1-9][0-9]*$~D', $path),
            VendorTransactionHttpService::class.'::updateStatusById' => $request->isMethod('POST') && 1 === preg_match('~^/api/vendor/transaction/status/[1-9][0-9]*$~D', $path),
            default => false,
        };
    }
}
