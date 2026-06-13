<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Interfacing;

use App\Vendoring\ServiceInterface\Interfacing\VendorInterfacingSurfaceRendererServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Thin renderer for the component-to-Interfacing convention.
 *
 * Vendoring prepares business data and then tries Interfacing-owned templates by noun surface.
 * If no candidate renders, Vendoring falls back to the Interfacing index mount and only then to
 * a standalone inline response.
 */
final readonly class VendorInterfacingSurfaceRendererService implements VendorInterfacingSurfaceRendererServiceInterface
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string>         $templateCandidates
     */
    public function renderOrJson(
        string $surfaceName,
        array $payload,
        array $templateCandidates,
        int $statusCode = Response::HTTP_OK,
    ): Response {
        foreach ($templateCandidates as $templateName) {
            $resolvedTemplateName = $this->buildTemplateName($templateName);

            try {
                $content = $this->twig->render($resolvedTemplateName, [
                    'surface' => $surfaceName,
                    'data' => $payload,
                    'payload' => $payload,
                    'vendor' => $payload,
                    'slots' => $payload['slots'] ?? [],
                    'slotMap' => $payload['slotMap'] ?? [],
                    'templateCandidates' => $templateCandidates,
                ]);
                $response = new Response($content, $statusCode);
            } catch (\Throwable) {
                continue;
            }

            if ($response instanceof Response) {
                $response->headers->set('X-Vendoring-Render-Mode', 'template');
                $response->headers->set('X-Vendoring-Template', $resolvedTemplateName);

                return $response;
            }
        }

        $fallbackTemplate = $this->buildTemplateName('@Interfacing/index/index');
        try {
            $content = $this->twig->render($fallbackTemplate, [
                'surface' => $surfaceName,
                'data' => $payload,
                'payload' => $payload,
                'fallbackPayload' => $payload,
                'fallbackTitle' => 'Vendor profile',
                'fallbackDescription' => 'Template lookup fell back to the Interfacing index mount.',
                'vendor' => $payload,
                'slots' => $payload['slots'] ?? [],
                'slotMap' => $payload['slotMap'] ?? [],
                'templateCandidates' => $templateCandidates,
            ]);
            $response = new Response($content, $statusCode);

            if ($response instanceof Response) {
                $response->headers->set('X-Vendoring-Render-Mode', 'template_fallback');
                $response->headers->set('X-Vendoring-Fallback-Reason', 'interfacing_template_not_found');
                $response->headers->set('X-Vendoring-Template', $fallbackTemplate);

                return $response;
            }
        } catch (\Throwable) {
            // Fall through to the standalone response below.
        }

        return new Response(
            '<!doctype html><html><head><meta charset="utf-8"><meta nameEntity="viewport" content="width=device-width, initial-scale=1"><title>Vendor profile</title></head><body><pre>'
            .htmlspecialchars(
                json_encode(
                    [
                        'surface' => $surfaceName,
                        'renderMode' => 'standalone_fallback',
                        'fallbackReason' => 'interfacing_index_missing',
                        'templateCandidates' => $templateCandidates,
                        'data' => $payload,
                    ],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
                ) ?: '{}',
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            )
            .'</pre></body></html>',
            $statusCode,
            [
                'X-Vendoring-Render-Mode' => 'standalone_fallback',
                'X-Vendoring-Fallback-Reason' => 'interfacing_index_missing',
                'X-Vendoring-Template' => 'standalone-inline',
            ],
        );
    }

    private function buildTemplateName(string $templateName): string
    {
        $templateName = trim($templateName);
        if ('' === $templateName) {
            return $templateName;
        }

        if (str_contains($templateName, '.html.twig')) {
            return $templateName;
        }

        return sprintf('%s.%s', $templateName, 'html.twig');
    }
}
