<?php

declare(strict_types=1);

namespace App\Vendoring\Support\Http;

use Symfony\Component\HttpFoundation\Request;

trait VendorBusinessOperationHttpTrait
{
    /** @return array<string, mixed> */
    private function payloadFromRequest(object $request): array
    {
        if (!$request instanceof Request) {
            return [];
        }
        $payload = [];
        $content = trim((string) $request->getContent());
        if ('' !== $content) {
            try {
                $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            } catch (\JsonException) {
                $payload = [];
            }
        }
        foreach ($request->request->all() as $key => $value) {
            if (is_string($key)) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    private function routeValue(object $request, string $nameEntity): ?string
    {
        if (!$request instanceof Request) {
            return null;
        }
        $value = $request->attributes->get($nameEntity);
        if (is_scalar($value) && '' !== trim((string) $value)) {
            return trim((string) $value);
        }
        $value = $request->query->get($nameEntity);
        if (is_scalar($value) && '' !== trim((string) $value)) {
            return trim((string) $value);
        }

        return null;
    }

    private function vendorReference(object $request): ?string
    {
        foreach (['vendorId', 'vendorSlug', 'id', 'slug', 'item'] as $field) {
            $value = $this->routeValue($request, $field);
            if (null !== $value) {
                return $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function acceptedSurface(string $surface, object $request, array $payload = []): array
    {
        return [
            'ok' => true,
            'component' => 'vendoring',
            'surface' => $surface,
            'vendorRef' => $this->vendorReference($request),
            'route' => $request instanceof Request ? $request->attributes->all() : [],
            'payload' => $payload,
        ];
    }
}
