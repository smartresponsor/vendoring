<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Interfacing;

use App\Vendoring\ServiceInterface\Interfacing\VendorInterfacingSurfaceRendererServiceInterface;
use App\Vendoring\ServiceInterface\Interfacing\VendorInterfacingTemplateCandidateProviderServiceInterface;
use App\Vendoring\ServiceInterface\Profile\VendorPublicProfileSummaryProviderServiceInterface;
use App\Vendoring\ValueObject\Interfacing\VendorInterfacingSurfaceNameValueObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * Host-facing Vendoring surfaces.
 *
 * The HTTP service prepares Vendoring-owned data and then searches for an Interfacing template using
 * the noun surface "vendor". Missing templates fall back to JSON by design.
 */
final class VendorInterfacingService
{
    public function __construct(
        private readonly VendorPublicProfileSummaryProviderServiceInterface $publicProfileSummaryProvider,
        private readonly VendorInterfacingTemplateCandidateProviderServiceInterface $templateCandidateProvider,
        private readonly VendorInterfacingSurfaceRendererServiceInterface $surfaceRenderer,
    ) {
    }

    public function index(): Response
    {
        return $this->renderSurface(
            VendorInterfacingSurfaceNameValueObject::INDEX,
            [
                'title' => 'Vendor',
                'publicName' => 'Vendor',
                'brandName' => 'Vendor',
                'summary' => 'Vendor landing surface rendered through the canonical CRUD index route.',
                'profileUrl' => '/vendor/',
                'vendorStatus' => 'ready',
                'profileStatus' => 'published',
                'avatar' => ['url' => null],
                'cover' => ['url' => null],
                'vendorId' => null,
            ],
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function renderSurface(string $surfaceName, array $payload): Response
    {
        $payload['slotMap'] = array_replace($payload['slotMap'] ?? [], $this->buildShellSlotMap($payload));
        $payload['slots'] = array_replace($payload['slots'] ?? [], $this->buildShellSlots($payload));

        return $this->surfaceRenderer->renderOrJson(
            surfaceName: $surfaceName,
            payload: $payload,
            templateCandidates: $this->templateCandidateProvider->candidatesFor($surfaceName),
        );
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, string>
     */
    private function buildShellSlotMap(array $payload): array
    {
        $publicName = (string) ($payload['publicName'] ?? 'Vendor');

        return [
            'shell.body.top' => 'Vendor landing',
            'shell.head.left.logo' => $publicName.' logo',
            'shell.head.left.nameEntity' => $publicName,
            'shell.head.left.title' => (string) ($payload['brandName'] ?? $publicName),
            'shell.head.context' => 'Vendor context',
            'shell.head.main' => 'Vendor index',
            'shell.head.right.user' => 'Vendor',
            'shell.head.right.cart' => 'Landing media',
            'shell.head.right.notification' => 'Surface status',
            'shell.head.right.toggle' => 'Toggle',
            'shell.head.bottom' => 'Header bottom',
            'shell.left.top' => 'Left top',
            'shell.left.middle' => 'Left middle',
            'shell.left.bottom' => 'Left bottom',
            'shell.context.top' => 'Context top',
            'shell.context.middle' => 'Context middle',
            'shell.context.bottom' => 'Context bottom',
            'shell.main.top' => 'Main top',
            'shell.main.content' => 'Main content',
            'shell.main.bottom' => 'Main bottom',
            'shell.right.top' => 'Right top',
            'shell.right.middle' => 'Right middle',
            'shell.right.bottom' => 'Right bottom',
            'shell.footer.top' => 'Footer top',
            'shell.footer.left' => 'Footer left',
            'shell.footer.context' => 'Footer context',
            'shell.footer.main' => 'Footer main',
            'shell.footer.right' => 'Footer right',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function buildShellSlots(array $payload): array
    {
        $publicName = (string) ($payload['publicName'] ?? 'Vendor');
        $brandName = (string) ($payload['brandName'] ?? $publicName);
        $vendorStatus = (string) ($payload['vendorStatus'] ?? 'unknown');
        $profileStatus = (string) ($payload['profileStatus'] ?? 'draft');
        $publishedAt = $payload['publishedAt'] ?? null;
        $vendorId = is_scalar($payload['vendorId'] ?? null) ? (string) ($payload['vendorId'] ?? '') : '';
        $profileUrl = '' !== $vendorId ? '/vendor/'.$vendorId : null;
        $homeUrl = '/vendor/';
        $avatarUrl = $this->resolveVendorAssetUrl($payload['avatar'] ?? null, $payload['avatarUrl'] ?? null);
        $coverUrl = $this->resolveVendorAssetUrl($payload['cover'] ?? null, $payload['coverUrl'] ?? null);
        $publishedLabel = null !== $publishedAt && '' !== (string) $publishedAt ? (string) $publishedAt : 'n/a';
        $isDetailSurface = '' !== $vendorId;
        $primaryLinkUrl = $isDetailSurface ? $profileUrl : $homeUrl;
        $primaryLinkLabel = $isDetailSurface ? 'My profile' : 'Vendor index';
        $primaryLinkDescription = $isDetailSurface ? $publicName : 'Vendor landing surface';

        return [
            'shell.body.top' => [[
                'type' => 'text',
                'label' => 'Vendor workspace',
                'description' => 'Profile information is rendered through canonical shell slots.',
            ]],
            'shell.head.left.logo' => [[
                'type' => 'media',
                'src' => $avatarUrl ?? '/mandala.svg',
                'alt' => $publicName,
                'label' => $publicName,
                'description' => $brandName,
            ]],
            'shell.head.left.nameEntity' => [[
                'type' => 'text',
                'label' => $publicName,
                'description' => 'Vendor public nameEntity',
            ]],
            'shell.head.left.title' => [[
                'type' => 'text',
                'label' => $brandName,
                'description' => 'Vendor brand',
            ]],
            'shell.head.context' => [
                ['type' => 'text', 'label' => 'Profile status', 'value' => $profileStatus],
                ['type' => 'text', 'label' => 'Vendor status', 'value' => $vendorStatus],
            ],
            'shell.head.main' => [[
                'type' => 'link',
                'label' => $isDetailSurface ? 'Open profile' : 'Open vendor index',
                'href' => $primaryLinkUrl,
                'description' => $isDetailSurface ? 'Canonical vendor detail route' : 'Canonical vendor index route',
            ]],
            'shell.head.right.user' => [[
                'type' => 'link',
                'label' => $primaryLinkLabel,
                'href' => $primaryLinkUrl,
                'description' => $primaryLinkDescription,
            ]],
            'shell.head.right.cart' => [
                ['type' => 'text', 'label' => 'Avatar', 'value' => null !== $avatarUrl ? 'available' : 'missing'],
                ['type' => 'text', 'label' => 'Cover', 'value' => null !== $coverUrl ? 'available' : 'missing'],
            ],
            'shell.head.right.notification' => [[
                'type' => 'text',
                'label' => 'Published',
                'value' => $publishedLabel,
            ]],
            'shell.head.right.toggle' => [[
                'type' => 'link',
                'label' => $primaryLinkLabel,
                'href' => $primaryLinkUrl,
                'description' => $isDetailSurface ? 'Open the current vendor profile' : 'Open the vendor index',
                'items' => [
                    [
                        'type' => 'link',
                        'label' => $primaryLinkLabel,
                        'href' => $primaryLinkUrl,
                    ],
                    [
                        'type' => 'link',
                        'label' => 'Vendor home',
                        'href' => $homeUrl,
                    ],
                ],
            ]],
            'shell.head.bottom' => [
                ['type' => 'text', 'label' => 'Vendor ID', 'value' => $vendorId ?: 'n/a'],
                ['type' => 'text', 'label' => 'Surface', 'value' => 'vendor'],
            ],
            'shell.left.top' => [
                ['type' => 'link', 'label' => 'Vendor home', 'href' => $homeUrl],
                ['type' => 'link', 'label' => $isDetailSurface ? 'Profile' : 'Index', 'href' => $primaryLinkUrl],
            ],
            'shell.left.middle' => [
                ['type' => 'link', 'label' => $isDetailSurface ? 'Open profile' : 'Open vendor index', 'href' => $primaryLinkUrl],
            ],
            'shell.left.bottom' => [
                ['type' => 'text', 'label' => 'Profile status', 'value' => $profileStatus],
                ['type' => 'text', 'label' => 'Vendor status', 'value' => $vendorStatus],
            ],
            'shell.context.top' => [
                ['type' => 'text', 'label' => 'Vendor ID', 'value' => $vendorId ?: 'n/a'],
                ['type' => 'text', 'label' => 'Published at', 'value' => $publishedLabel],
            ],
            'shell.context.middle' => [
                ['type' => 'text', 'label' => 'Avatar', 'value' => null !== $avatarUrl ? 'available' : 'missing'],
                ['type' => 'text', 'label' => 'Cover', 'value' => null !== $coverUrl ? 'available' : 'missing'],
            ],
            'shell.context.bottom' => [
                ['type' => 'text', 'label' => 'Brand', 'value' => $brandName],
            ],
            'shell.main.top' => [
                ['type' => 'media', 'src' => $coverUrl ?? $avatarUrl ?? '/mandala.svg', 'alt' => $publicName.' cover', 'label' => $publicName, 'description' => $brandName],
                ['type' => 'text', 'label' => 'Public nameEntity', 'value' => $publicName],
                ['type' => 'text', 'label' => 'Brand nameEntity', 'value' => $brandName],
            ],
            'shell.main.content' => [
                ['type' => 'text', 'label' => 'Profile status', 'value' => $profileStatus],
                ['type' => 'text', 'label' => 'Vendor status', 'value' => $vendorStatus],
                ['type' => 'link', 'label' => $isDetailSurface ? 'Profile URL' : 'Index URL', 'href' => $primaryLinkUrl, 'value' => $primaryLinkUrl],
                ['type' => 'text', 'label' => 'Avatar', 'value' => null !== $avatarUrl ? $avatarUrl : 'missing'],
                ['type' => 'text', 'label' => 'Cover', 'value' => null !== $coverUrl ? $coverUrl : 'missing'],
            ],
            'shell.main.bottom' => [
                ['type' => 'text', 'label' => 'Summary', 'value' => (string) ($payload['summary'] ?? 'Vendor public profile')],
            ],
            'shell.right.top' => [
                ['type' => 'text', 'label' => 'Avatar', 'value' => null !== $avatarUrl ? 'available' : 'missing'],
            ],
            'shell.right.middle' => [
                ['type' => 'text', 'label' => 'Cover', 'value' => null !== $coverUrl ? 'available' : 'missing'],
            ],
            'shell.right.bottom' => [
                ['type' => 'text', 'label' => 'Status', 'value' => $vendorStatus],
            ],
            'shell.footer.top' => [
                ['type' => 'text', 'label' => 'Vendor', 'value' => $publicName],
            ],
            'shell.footer.left' => [
                ['type' => 'link', 'label' => $isDetailSurface ? 'Profile' : 'Index', 'href' => $primaryLinkUrl],
            ],
            'shell.footer.context' => [
                ['type' => 'text', 'label' => 'Profile status', 'value' => $profileStatus],
            ],
            'shell.footer.main' => [
                ['type' => 'text', 'label' => 'Owner-side surface', 'value' => 'vendor index'],
            ],
            'shell.footer.right' => [
                ['type' => 'text', 'label' => 'Base', 'value' => 'Interfacing root base'],
            ],
        ];
    }

    private function resolveVendorAssetUrl(mixed $primary, mixed $secondary): ?string
    {
        if (is_array($primary) && isset($primary['url']) && is_string($primary['url']) && '' !== $primary['url']) {
            return $primary['url'];
        }

        if (is_string($primary) && '' !== $primary) {
            return $primary;
        }

        if (is_string($secondary) && '' !== $secondary) {
            return $secondary;
        }

        return null;
    }
}
