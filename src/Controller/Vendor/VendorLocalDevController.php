<?php

declare(strict_types=1);

namespace App\Vendoring\Controller\Vendor;

use App\Vendoring\ServiceInterface\Runtime\VendorAppEnvResolverServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VendorLocalDevController
{
    private const string HOME_PAGE = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendoring Local Dev</title>
  </head>
  <body>
    <main>
      <h1>Vendoring Local Dev</h1>
      <p>Local runtime is up.</p>
      <ul>
        <li><a href="/healthz">Health endpoint</a></li>
        <li><a href="/ops/vendor-transactions/vendor-demo">Operator page</a></li>
        <li><a href="/api/vendor-transactions/vendor/vendor-demo">Vendor transactions API</a></li>
      </ul>
    </main>
  </body>
</html>
HTML;

    public function __construct(
        private readonly VendorAppEnvResolverServiceInterface $appEnvResolver,
    ) {}

    #[Route('/', name: 'app_local_dev_home', methods: ['GET'])]
    public function home(): Response
    {
        return new Response(self::HOME_PAGE);
    }

    #[Route('/healthz', name: 'app_local_dev_healthz', methods: ['GET'])]
    public function healthz(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'appEnv' => $this->appEnvResolver->resolve(),
        ]);
    }

}
