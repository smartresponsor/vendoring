<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Controller;

use App\Vendoring\Controller\Vendor\VendorProfileController;
use App\Vendoring\DTO\VendorProfileDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Projection\Vendor\VendorProfileView;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\Service\Profile\VendorProfileRequestResolverService;
use App\Vendoring\ServiceInterface\Profile\VendorProfileServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorProfileViewBuilderServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorProfileControllerTest extends TestCase
{
    public function testShowReturnsNotFoundWhenVendorProfileViewIsMissing(): void
    {
        $controller = new VendorProfileController(
            new FakeVendorRepository(null),
            new FakeVendorProfileService(),
            new FakeVendorProfileViewBuilder(null),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->show(404);
        $payload = self::decodePayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('vendor_not_found', $payload['error']);
    }

    public function testShowReturnsProfileReadinessPayload(): void
    {
        $view = new VendorProfileView(
            vendorId: 12,
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
                'seoDescription' => 'About brand',
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
                'status' => 'published',
                'publishedAt' => '2025-01-31T00:00:00+00:00',
            ],
            searchProfile: [
                'seoTitle' => 'Brand',
                'seoDescription' => 'About brand',
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
        );

        $controller = new VendorProfileController(
            new FakeVendorRepository(new VendorEntity('Brand')),
            new FakeVendorProfileService(),
            new FakeVendorProfileViewBuilder($view),
            new VendorProfileRequestResolverService(),
        );
        $response = $controller->show(12);
        $payload = self::decodePayload($response);
        $data = self::decodeData($payload);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(100, $data['completionPercent']);
        self::assertTrue($data['readyForPublishing']);
        self::assertNull($data['nextAction']);
    }

    public function testUpdateReturnsNotFoundWhenVendorDoesNotExist(): void
    {
        $controller = new VendorProfileController(
            new FakeVendorRepository(null),
            new FakeVendorProfileService(),
            new FakeVendorProfileViewBuilder(null),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->update(404, Request::create('/', 'PATCH', content: json_encode(['displayName' => 'Brand'], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('vendor_not_found', $payload['error']);
    }

    public function testUpdateReturnsBadRequestForMalformedJson(): void
    {
        $controller = new VendorProfileController(
            new FakeVendorRepository(new VendorEntity('Brand')),
            new FakeVendorProfileService(),
            new FakeVendorProfileViewBuilder(null),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->update(12, Request::create('/', 'PATCH', content: '{invalid-json'));
        $payload = self::decodePayload($response);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('malformed_json', $payload['error']);
    }

    public function testUpdateRejectsNonObjectSocials(): void
    {
        $controller = new VendorProfileController(
            new FakeVendorRepository(new VendorEntity('Brand')),
            new FakeVendorProfileService(),
            new FakeVendorProfileViewBuilder(null),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->update(12, Request::create('/', 'PATCH', content: json_encode(['socials' => 'x'], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('socials_must_be_object', $payload['error']);
    }

    public function testUpdateUsesPathVendorIdAndReturnsUpdatedReadinessPayload(): void
    {
        $vendor = new VendorEntity('Brand');
        $profileService = new FakeVendorProfileService();
        $view = new VendorProfileView(
            vendorId: 12,
            brandName: 'Brand',
            vendorStatus: 'active',
            profile: [
                'brandName' => 'Brand',
                'vendorStatus' => 'active',
                'ownerUserId' => 7,
                'displayName' => 'VendorEntity Profile',
                'publicDisplayName' => 'VendorEntity Profile',
                'about' => 'About vendor',
                'website' => 'https://vendor.example',
                'socials' => ['x' => 'https://x.example/vendor'],
                'seoTitle' => 'VendorEntity Profile',
                'seoDescription' => 'About vendor',
            ],
            businessProfile: [
                'brandName' => 'Brand',
                'vendorStatus' => 'active',
                'ownerUserId' => 7,
            ],
            publicProfile: [
                'displayName' => 'VendorEntity Profile',
                'publicDisplayName' => 'VendorEntity Profile',
                'about' => 'About vendor',
                'website' => 'https://vendor.example',
                'socials' => ['x' => 'https://x.example/vendor'],
                'status' => 'published',
                'publishedAt' => '2025-01-31T00:00:00+00:00',
            ],
            searchProfile: [
                'seoTitle' => 'VendorEntity Profile',
                'seoDescription' => 'About vendor',
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
        );

        $controller = new VendorProfileController(
            new FakeVendorRepository($vendor),
            $profileService,
            new FakeVendorProfileViewBuilder($view),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->update(12, Request::create('/', 'PATCH', content: json_encode([
            'vendorId' => 999,
            'displayName' => 'VendorEntity Profile',
            'about' => 'About vendor',
            'website' => 'https://vendor.example',
            'socials' => ['x' => 'https://x.example/vendor'],
            'seoTitle' => 'VendorEntity Profile',
            'seoDescription' => 'About vendor',
        ], JSON_THROW_ON_ERROR)));
        $payload = self::decodePayload($response);
        $data = self::decodeData($payload);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($profileService->lastDto);
        self::assertSame(12, $profileService->lastDto->vendorId);
        self::assertSame('VendorEntity Profile', $profileService->lastDto->displayName);
        self::assertSame(['x' => 'https://x.example/vendor'], $profileService->lastDto->socials);
        self::assertSame(100, $data['completionPercent']);
    }

    public function testUpdatePassesPublicationActionThroughToService(): void
    {
        $vendor = new VendorEntity('Brand');
        $profileService = new FakeVendorProfileService();
        $view = new VendorProfileView(
            vendorId: 12,
            brandName: 'Brand',
            vendorStatus: 'active',
            profile: [
                'brandName' => 'Brand',
                'vendorStatus' => 'active',
                'ownerUserId' => 7,
                'publicProfileStatus' => 'published',
                'publicProfilePublishedAt' => '2025-01-31T00:00:00+00:00',
                'displayName' => 'VendorEntity Profile',
                'publicDisplayName' => 'VendorEntity Profile',
                'about' => 'About vendor',
                'website' => 'https://vendor.example',
                'socials' => ['x' => 'https://x.example/vendor'],
                'seoTitle' => 'VendorEntity Profile',
                'seoDescription' => 'About vendor',
            ],
            businessProfile: [
                'brandName' => 'Brand',
                'vendorStatus' => 'active',
                'ownerUserId' => 7,
            ],
            publicProfile: [
                'displayName' => 'VendorEntity Profile',
                'publicDisplayName' => 'VendorEntity Profile',
                'about' => 'About vendor',
                'website' => 'https://vendor.example',
                'socials' => ['x' => 'https://x.example/vendor'],
                'status' => 'published',
                'publishedAt' => '2025-01-31T00:00:00+00:00',
            ],
            searchProfile: [
                'seoTitle' => 'VendorEntity Profile',
                'seoDescription' => 'About vendor',
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
        );

        $controller = new VendorProfileController(
            new FakeVendorRepository($vendor),
            $profileService,
            new FakeVendorProfileViewBuilder($view),
            new VendorProfileRequestResolverService(),
        );

        $response = $controller->update(12, Request::create('/', 'PATCH', content: json_encode([
            'displayName' => 'VendorEntity Profile',
            'about' => 'About vendor',
            'website' => 'https://vendor.example',
            'socials' => ['x' => 'https://x.example/vendor'],
            'seoTitle' => 'VendorEntity Profile',
            'seoDescription' => 'About vendor',
            'publicationAction' => 'publish',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($profileService->lastDto);
        self::assertSame('publish', $profileService->lastDto->publicationAction);
    }

    /** @return array<string, mixed> */
    private static function decodePayload(JsonResponse $response): array
    {
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private static function decodeData(array $payload): array
    {
        $data = $payload['data'] ?? null;

        /** @var array<string, mixed> $data */
        return $data;
    }
}

final class FakeVendorRepository implements VendorRepositoryInterface
{
    public function __construct(private readonly ?VendorEntity $vendor) {}

    public function find(mixed $id, mixed $lockMode = null, mixed $lockVersion = null): ?object
    {
        return $this->vendor;
    }

    public function findAll(): array
    {
        return null === $this->vendor ? [] : [$this->vendor];
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findAll();
    }

    public function findOneBy(array $criteria): ?object
    {
        return $this->vendor;
    }

    public function getClassName(): string
    {
        return VendorEntity::class;
    }
}

final class FakeVendorProfileService implements VendorProfileServiceInterface
{
    public ?VendorProfileDTO $lastDto = null;
    public ?VendorEntity $lastVendor = null;

    public function upsert(VendorEntity $vendor, VendorProfileDTO $dto): \App\Vendoring\Entity\Vendor\VendorProfileEntity
    {
        $this->lastVendor = $vendor;
        $this->lastDto = $dto;

        return new \App\Vendoring\Entity\Vendor\VendorProfileEntity($vendor);
    }
}

final class FakeVendorProfileViewBuilder implements VendorProfileViewBuilderServiceInterface
{
    public function __construct(private readonly ?VendorProfileView $view) {}

    public function buildForVendorId(int $vendorId): ?VendorProfileView
    {
        return $this->view;
    }
}
