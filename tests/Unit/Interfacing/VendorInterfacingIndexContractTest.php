<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Interfacing;

use App\Vendoring\ProviderInterface\Interfacing\VendorInterfacingTemplateCandidateProviderInterface;
use App\Vendoring\Service\Interfacing\VendorInterfacingService;
use App\Vendoring\ServiceInterface\Interfacing\VendorInterfacingSurfaceRendererServiceInterface;
use App\Vendoring\ValueObject\Interfacing\VendorInterfacingSurfaceNameValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class VendorInterfacingIndexContractTest extends TestCase
{
    public function testIndexRemainsGenericAndDoesNotExposeActorIdentity(): void
    {
        $provider = $this->createMock(VendorInterfacingTemplateCandidateProviderInterface::class);
        $provider->expects(self::once())->method('candidatesFor')
            ->with(VendorInterfacingSurfaceNameValueObject::INDEX)->willReturn(['vendor/index.html.twig']);
        $renderer = $this->createMock(VendorInterfacingSurfaceRendererServiceInterface::class);
        $response = new JsonResponse(['ok' => true]);
        $renderer->expects(self::once())->method('renderOrJson')
            ->with(
                VendorInterfacingSurfaceNameValueObject::INDEX,
                self::callback(static function (array $payload): bool {
                    self::assertSame('Vendor', $payload['publicName']);
                    self::assertNull($payload['vendorId']);
                    self::assertSame('/vendor/', $payload['profileUrl']);
                    self::assertSame(['url' => null], $payload['avatar']);
                    self::assertIsArray($payload['slots']);
                    $userSlot = $payload['slots']['shell.head.right.user'];
                    self::assertIsArray($userSlot);
                    self::assertIsArray($userSlot[0]);
                    self::assertSame('/vendor/', $userSlot[0]['href']);

                    return true;
                }),
                ['vendor/index.html.twig'],
            )->willReturn($response);
        self::assertSame($response, (new VendorInterfacingService($provider, $renderer))->index());
    }
}
