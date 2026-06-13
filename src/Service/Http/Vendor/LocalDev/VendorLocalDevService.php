<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\LocalDev;

use App\Vendoring\ServiceInterface\Runtime\VendorAppEnvResolverServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class VendorLocalDevService
{
    public function __construct(
        private readonly VendorAppEnvResolverServiceInterface $appEnvResolver,
        private readonly Environment $twig,
    ) {
    }

    public function home(): Response
    {
        $template = sprintf('%s.%s', 'vendor/local_dev/home', 'html.twig');

        return new Response($this->twig->render($template), Response::HTTP_OK);
    }

    public function healthz(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'appEnv' => $this->appEnvResolver->resolve(),
        ]);
    }
}
