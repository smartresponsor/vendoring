<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Service\Vendor\VendorSecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class VendorAuthMiddleware implements HttpKernelInterface
{
    public function __construct(
        private readonly HttpKernelInterface $app,
        private readonly VendorSecurityService $security
    ) {}

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true)
    {
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/api/vendor/')) {
            return $this->app->handle($request, $type, $catch);
        }

        $auth = $request->headers->get('Authorization', '');
        if (!preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $token = $m[1];
        $vendor = $this->security->validateToken($token);
        if (!$vendor) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        // attach vendor id to request attributes for controllers
        $request->attributes->set('vendorId', $vendor->getId());

        return $this->app->handle($request, $type, $catch);
    }
}
