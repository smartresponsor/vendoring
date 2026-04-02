<?php

declare(strict_types=1);

namespace App\Tests\Unit\Ops;

use App\Projection\VendorExternalIntegrationRuntimeView;
use App\Projection\VendorFinanceRuntimeView;
use App\Projection\VendorOwnershipView;
use App\Projection\VendorProfileView;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\Service\Ops\VendorRuntimeStatusViewBuilder;
use App\ServiceInterface\Integration\VendorExternalIntegrationRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorFinanceRuntimeViewBuilderInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use App\ServiceInterface\VendorProfileViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorRuntimeStatusViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownershipViewBuilder;
    private VendorProfileViewBuilderInterface&MockObject $profileViewBuilder;
    private VendorFinanceRuntimeViewBuilderInterface&MockObject $financeRuntimeViewBuilder;
    private VendorStatementDeliveryRuntimeViewBuilderInterface&MockObject $statementDeliveryRuntimeViewBuilder;
    private VendorExternalIntegrationRuntimeViewBuilderInterface&MockObject $externalIntegrationRuntimeViewBuilder;

    protected function setUp(): void
    {
        $this->ownershipViewBuilder = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->profileViewBuilder = $this->createMock(VendorProfileViewBuilderInterface::class);
        $this->financeRuntimeViewBuilder = $this->createMock(VendorFinanceRuntimeViewBuilderInterface::class);
        $this->statementDeliveryRuntimeViewBuilder = $this->createMock(VendorStatementDeliveryRuntimeViewBuilderInterface::class);
        $this->externalIntegrationRuntimeViewBuilder = $this->createMock(VendorExternalIntegrationRuntimeViewBuilderInterface::class);
    }

    public function testBuildIncludesProfileSurfaceForNumericVendorId(): void
    {
        $this->ownershipViewBuilder
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(42)
            ->willReturn(new VendorOwnershipView(42, 7, []));

        $this->profileViewBuilder
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(42)
            ->willReturn(new VendorProfileView(
                vendorId: 42,
                brandName: 'Brand',
                vendorStatus: 'active',
                profile: [
                    'brandName' => 'Brand',
                    'vendorStatus' => 'active',
                    'ownerUserId' => 7,
                    'displayName' => 'Brand',
                    'publicDisplayName' => 'Brand',
                    'about' => 'About',
                    'website' => 'https://vendor.example',
                    'socials' => ['x' => 'https://x.example/vendor'],
                    'seoTitle' => 'Brand',
                    'seoDescription' => 'About',
                ],
                businessProfile: [
                    'brandName' => 'Brand',
                    'vendorStatus' => 'active',
                    'ownerUserId' => 7,
                ],
                publicProfile: [
                    'displayName' => 'Brand',
                    'publicDisplayName' => 'Brand',
                    'about' => 'About',
                    'website' => 'https://vendor.example',
                    'socials' => ['x' => 'https://x.example/vendor'],
                ],
                searchProfile: [
                    'seoTitle' => 'Brand',
                    'seoDescription' => 'About',
                ],
                publication: [
                    'status' => 'published',
                    'publishedAt' => '2025-01-31T00:00:00+00:00',
                    'canPublish' => true,
                ],
                sections: [
                    'business' => ['label' => 'Business profile', 'complete' => true, 'missing' => []],
                    'public' => ['label' => 'Public profile', 'complete' => true, 'missing' => []],
                    'search' => ['label' => 'Search metadata', 'complete' => true, 'missing' => []],
                ],
                completionPercent: 100,
                readyForPublishing: true,
                nextAction: null,
            ));

        $this->financeRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorFinanceRuntimeView('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['gmv' => 1000], ['provider' => 'bank'], ['closing' => 900]));

        $this->statementDeliveryRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorStatementDeliveryRuntimeView('tenant-1', '42', 'USD', ['ownerUserId' => 7], ['closing' => 900], ['path' => '/tmp/statement.csv'], [['email' => 'ops@example.com']]));

        $this->externalIntegrationRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42')
            ->willReturn(new VendorExternalIntegrationRuntimeView('tenant-1', '42', ['ownerUserId' => 7], ['crm' => 'hubspot'], ['webhook' => 'ok'], ['payoutProvider' => 'bank'], ['crm', 'webhooks']));

        $payload = $this->buildService()->build('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')->toArray();
        $profile = self::assertArrayPayload($payload['profile'] ?? null);
        $businessProfile = self::assertArrayPayload($profile['businessProfile'] ?? null);
        $publicProfile = self::assertArrayPayload($profile['publicProfile'] ?? null);

        self::assertTrue($payload['surfaceStatus']['profile']);
        self::assertSame(100, $profile['completionPercent']);
        self::assertSame('Brand', $businessProfile['brandName']);
        self::assertSame('https://vendor.example', $publicProfile['website']);
    }

    public function testBuildSkipsOwnershipAndProfileForNonNumericVendorId(): void
    {
        $this->ownershipViewBuilder->expects(self::never())->method('buildForVendorId');
        $this->profileViewBuilder->expects(self::never())->method('buildForVendorId');

        $this->financeRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn(new VendorFinanceRuntimeView('tenant-1', 'vendor-abc', 'USD', null, [], null, null));

        $this->statementDeliveryRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn(new VendorStatementDeliveryRuntimeView('tenant-1', 'vendor-abc', 'USD', null, [], null, []));

        $this->externalIntegrationRuntimeViewBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn(new VendorExternalIntegrationRuntimeView('tenant-1', 'vendor-abc', null, [], [], [], []));

        $payload = $this->buildService()->build('tenant-1', 'vendor-abc')->toArray();

        self::assertNull($payload['ownership']);
        self::assertNull($payload['profile']);
        self::assertFalse($payload['surfaceStatus']['ownership']);
        self::assertFalse($payload['surfaceStatus']['profile']);
    }

    private function buildService(): VendorRuntimeStatusViewBuilder
    {
        return new VendorRuntimeStatusViewBuilder(
            $this->ownershipViewBuilder,
            $this->profileViewBuilder,
            $this->financeRuntimeViewBuilder,
            $this->statementDeliveryRuntimeViewBuilder,
            $this->externalIntegrationRuntimeViewBuilder,
        );
    }

    /** @return array<string, mixed> */
    private static function assertArrayPayload(mixed $value): array
    {
        self::assertIsArray($value);

        return $value;
    }
}
