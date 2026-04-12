<?php

declare(strict_types=1);

namespace Symfony\Component\Panther;

use Symfony\Component\HttpFoundation\Response;

/**
 * Static-analysis fallback for environments where symfony/panther is not installed.
 * Runtime browser execution should use the real Panther package.
 */
class Client
{
    /** @noinspection PhpTooManyParametersInspection */
    public function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], ?string $content = null): void
    {
        unset($method, $uri, $parameters, $files, $server, $content);
    }

    public function getInternalResponse(): Response
    {
        return new Response();
    }

    public function getPageSource(): string
    {
        return '';
    }

    public function quit(): void
    {
    }
}
