<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorProfile;
use App\Vendoring\RepositoryInterface\Vendor\VendorProfileRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\Service\Profile\VendorProfileViewBuilderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorProfileViewBuilderTest extends TestCase
{
    private VendorRepositoryInterface&MockObject $vendorRepository;
    private VendorProfileRepositoryInterface&MockObject $profileRepository;

    protected function setUp(): void
    {
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->profileRepository = $this->createMock(VendorProfileRepositoryInterface::class);
    }

    public function testBuildForVendorIdReturnsNullWhenVendorDoesNotExist(): void
    {
        $this->vendorRepository->expects(self::once())->method('find')->with(404)->willReturn(null);
        $this->profileRepository->expects(self::never())->method('findOneBy');

        self::assertNull($this->buildService()->buildForVendorId(404));
    }

    public function testBuildForVendorIdReportsMissingProfileFieldsAsNextAction(): void
    {
        $vendor = new Vendor('Brand Name');

        $this->forceId($vendor, 7);
        $this->vendorRepository->expects(self::once())->method('find')->with(7)->willReturn($vendor);
        $this->profileRepository->expects(self::once())->method('findOneBy')->with(['vendor' => $vendor])->willReturn(null);

        $view = $this->buildService()->buildForVendorId(7);

        self::assertNotNull($view);
        $payload = $view->toArray();

        self::assertSame(13, $payload['completionPercent']);
        self::assertFalse($payload['readyForPublishing']);
        self::assertSame('assign_owner', $payload['nextAction']);
        self::assertSame('Brand Name', $payload['profile']['publicDisplayName']);
        self::assertSame(['brandName' => 'Brand Name', 'vendorStatus' => 'inactive', 'ownerUserId' => null], $payload['businessProfile']);
        self::assertSame(['ownerUserId'], $payload['sections']['business']['missing']);
        self::assertSame(['status' => 'draft', 'publishedAt' => null, 'canPublish' => false], $payload['publication']);
    }

    public function testBuildForVendorIdMarksCompleteProfileAsReadyForPublishing(): void
    {
        $vendor = new Vendor('Brand Name', 42);
        $profile = new VendorProfile($vendor);
        $profile->updateProfile(
            displayName: 'Vendor Portal',
            about: 'High-quality home goods.',
            website: 'https://vendor.example',
            socials: ['x' => 'https://x.example/vendor'],
            seoTitle: 'Vendor Portal Home Goods',
            seoDescription: 'Home goods profile',
        );

        $this->forceId($vendor, 9);
        $vendor->activate();

        $this->vendorRepository->expects(self::once())->method('find')->with(9)->willReturn($vendor);
        $this->profileRepository->expects(self::once())->method('findOneBy')->with(['vendor' => $vendor])->willReturn($profile);

        $payload = $this->buildService()->buildForVendorId(9)?->toArray();

        self::assertIsArray($payload);
        self::assertSame(100, $payload['completionPercent']);
        self::assertTrue($payload['readyForPublishing']);
        self::assertNull($payload['nextAction']);
        self::assertSame('active', $payload['vendorStatus']);
        self::assertSame(['brandName' => 'Brand Name', 'vendorStatus' => 'active', 'ownerUserId' => 42], $payload['businessProfile']);
        self::assertSame('draft', $payload['publication']['status']);
        self::assertTrue($payload['publication']['canPublish']);
        self::assertSame(['displayName' => 'Vendor Portal', 'publicDisplayName' => 'Vendor Portal', 'about' => 'High-quality home goods.', 'website' => 'https://vendor.example', 'socials' => ['x' => 'https://x.example/vendor'], 'status' => 'draft', 'publishedAt' => null], $payload['publicProfile']);
        self::assertSame([], $payload['sections']['search']['missing']);
    }

    private function buildService(): VendorProfileViewBuilderService
    {
        return new VendorProfileViewBuilderService($this->vendorRepository, $this->profileRepository);
    }

    private function forceId(Vendor $vendor, int $id): void
    {
        $reflection = new \ReflectionProperty($vendor, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($vendor, $id);
    }
}
