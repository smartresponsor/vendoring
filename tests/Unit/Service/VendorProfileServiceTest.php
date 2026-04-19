<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorProfileDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorProfile;
use App\Vendoring\RepositoryInterface\VendorProfileRepositoryInterface;
use App\Vendoring\Service\VendorProfileService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorProfileServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private VendorProfileRepositoryInterface&MockObject $repository;
    private EventDispatcherInterface&MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(VendorProfileRepositoryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    public function testUpsertNormalizesWhitespaceOnlyFieldsAndSocials(): void
    {
        $vendor = new Vendor('Smartresponsor');
        $dto = new VendorProfileDTO(
            vendorId: 1,
            displayName: '  Vendor Portal  ',
            about: '   ',
            website: ' https://vendor.example ',
            socials: [' x ' => ' https://x.example/vendor ', 'empty' => '   ', '   ' => 'ignored'],
            seoTitle: ' SEO Title ',
            seoDescription: '   ',
        );

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['vendor' => $vendor])
            ->willReturn(null);

        $this->entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(VendorProfile::class));
        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::once())->method('dispatch');

        $service = $this->buildService();
        $profile = $service->upsert($vendor, $dto);

        self::assertSame('Vendor Portal', $profile->getDisplayName());
        self::assertNull($profile->getAbout());
        self::assertSame('https://vendor.example', $profile->getWebsite());
        self::assertSame(['x' => 'https://x.example/vendor'], $profile->getSocials());
        self::assertSame('SEO Title', $profile->getSeoTitle());
        self::assertNull($profile->getSeoDescription());
        self::assertSame('draft', $profile->getPublicProfileStatus());
    }

    public function testUpsertUpdatesExistingProfileWithoutReflectionFlow(): void
    {
        $vendor = new Vendor('Brand');
        $existing = new VendorProfile($vendor);
        $existing->updateProfile('Old', 'Old about', null, null, null, null);

        $dto = new VendorProfileDTO(vendorId: 1, displayName: ' New ', about: ' Better ');

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['vendor' => $vendor])
            ->willReturn($existing);

        $this->entityManager->expects(self::once())->method('persist')->with($existing);
        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::once())->method('dispatch');

        $profile = $this->buildService()->upsert($vendor, $dto);

        self::assertSame($existing, $profile);
        self::assertSame('New', $profile->getDisplayName());
        self::assertSame('Better', $profile->getAbout());
    }

    public function testUpsertPublishesCompleteProfileWhenRequested(): void
    {
        $vendor = new Vendor('Brand');
        $dto = new VendorProfileDTO(
            vendorId: 1,
            displayName: 'Vendor Profile',
            about: 'About vendor',
            website: 'https://vendor.example',
            socials: ['x' => 'https://x.example/vendor'],
            seoTitle: 'Vendor Profile',
            seoDescription: 'About vendor',
            publicationAction: 'publish',
        );

        $this->repository->expects(self::once())->method('findOneBy')->with(['vendor' => $vendor])->willReturn(null);
        $this->entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(VendorProfile::class));
        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::once())->method('dispatch');

        $profile = $this->buildService()->upsert($vendor, $dto);

        self::assertSame('published', $profile->getPublicProfileStatus());
        self::assertNotNull($profile->getPublicProfilePublishedAt());
    }

    public function testUpsertRejectsPublishWhenPublicProfileIncomplete(): void
    {
        $vendor = new Vendor('Brand');
        $dto = new VendorProfileDTO(vendorId: 1, displayName: 'Vendor Profile', publicationAction: 'publish');

        $this->repository->expects(self::once())->method('findOneBy')->with(['vendor' => $vendor])->willReturn(null);
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('public_profile_incomplete');

        $this->buildService()->upsert($vendor, $dto);
    }

    private function buildService(): VendorProfileService
    {
        return new VendorProfileService($this->entityManager, $this->repository, $this->dispatcher);
    }
}
