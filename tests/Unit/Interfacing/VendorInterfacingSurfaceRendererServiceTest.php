<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Interfacing;

use App\Vendoring\Service\Interfacing\VendorInterfacingSurfaceRendererService;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class VendorInterfacingSurfaceRendererServiceTest extends TestCase
{
    public function testItRendersExistingTemplate(): void
    {
        $twig = new Environment(new ArrayLoader([
            'vendor/profile/show.html.twig' => 'Vendor {{ data.publicName }}',
        ]));
        $renderer = new VendorInterfacingSurfaceRendererService($twig);

        $response = $renderer->renderOrJson('vendor.profile', ['publicName' => 'Acme'], ['vendor/profile/show.html.twig']);

        self::assertSame('template', $response->headers->get('X-Vendoring-Render-Mode'));
        self::assertSame('Vendor Acme', $response->getContent());
    }

    public function testItReturnsJsonWhenTemplateIsMissing(): void
    {
        $twig = new Environment(new ArrayLoader([
            '@Interfacing/index/index.html.twig' => 'fallback {{ fallbackPayload.publicName }}',
        ]));
        $renderer = new VendorInterfacingSurfaceRendererService($twig);

        $response = $renderer->renderOrJson('vendor.profile', ['publicName' => 'Acme'], ['missing.html.twig']);

        self::assertSame('template_fallback', $response->headers->get('X-Vendoring-Render-Mode'));
        self::assertSame('interfacing_template_not_found', $response->headers->get('X-Vendoring-Fallback-Reason'));
        self::assertSame('@Interfacing/index/index.html.twig', $response->headers->get('X-Vendoring-Template'));
        self::assertSame('fallback Acme', $response->getContent());
    }
}
