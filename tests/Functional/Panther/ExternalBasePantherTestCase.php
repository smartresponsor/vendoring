<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Functional\Panther;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class ExternalBasePantherTestCase extends PantherTestCase
{
    protected static function createExternalBaseClient(): Client
    {
        $baseUriValue = $_SERVER['PANTHER_EXTERNAL_BASE_URI'] ?? $_ENV['PANTHER_EXTERNAL_BASE_URI'] ?? '';
        $baseUri = is_scalar($baseUriValue) ? (string) $baseUriValue : '';
        $baseUri = rtrim(trim($baseUri), '/');

        if ('' === $baseUri) {
            self::markTestSkipped('Set PANTHER_EXTERNAL_BASE_URI to run Vendoring Panther smoke tests.');
        }

        return self::createPantherClient([
            'external_base_uri' => $baseUri,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected static function decodeJsonResponse(Client $client, int $expectedStatusCode = 200): array
    {
        $response = $client->getInternalResponse();

        self::assertSame($expectedStatusCode, $response->getStatusCode());
        self::assertNotFalse($response->getContent());

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $payload;
    }
}
