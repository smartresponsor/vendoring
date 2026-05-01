<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorCategoryEntity;
use App\Vendoring\Entity\Vendor\VendorCodeStorageEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionHistoryEntity;
use App\Vendoring\Entity\Vendor\VendorConversationEntity;
use App\Vendoring\Entity\Vendor\VendorConversationMessageEntity;
use App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use App\Vendoring\Entity\Vendor\VendorDocumentAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorFavouriteEntity;
use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\Entity\Vendor\VendorIbanEntity;
use App\Vendoring\Entity\Vendor\VendorLogEntity;
use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use App\Vendoring\Entity\Vendor\VendorMediaAttachmentEntity;
use App\Vendoring\Entity\Vendor\VendorPaymentEntity;
use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use App\Vendoring\Entity\Vendor\VendorProfileAvatarEntity;
use App\Vendoring\Entity\Vendor\VendorProfileCoverEntity;
use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\Entity\Vendor\VendorShipmentEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistItemEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VendorOwnershipDemoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($index = 1; $index <= 6; ++$index) {
            $vendor = new VendorEntity(sprintf('Vendor Demo %02d', $index), 1000 + $index);
            if (0 !== $index % 3) {
                $vendor->activate();
            }

            $manager->persist($vendor);

            $profile = new VendorProfileEntity($vendor);
            $profile->updateProfile(
                displayName: sprintf('Vendor Demo %02d LLC', $index),
                about: sprintf('Deterministic demo vendor profile %02d for host fixtures.', $index),
                website: sprintf('https://vendor-demo-%02d.example.test', $index),
                socials: ['instagram' => sprintf('@vendor_demo_%02d', $index)],
                seoTitle: sprintf('Vendor Demo %02d', $index),
                seoDescription: sprintf('Deterministic SEO description for vendor %02d.', $index),
            );
            $manager->persist($profile);

            $billing = new VendorBillingEntity($vendor);
            $billing->update(
                iban: sprintf('DE893704004405320130%02d', $index),
                swift: 'DEUTDEFF',
                payoutMethod: 0 === $index % 2 ? 'bank' : 'wire',
                billingEmail: sprintf('billing+%02d@example.test', $index),
            );
            $manager->persist($billing);
            $manager->persist(new VendorIbanEntity($vendor, sprintf('DE893704004405320130%02d', $index), 'DEUTDEFF'));

            $media = new VendorMediaEntity($vendor);
            $media->update(
                logoPath: sprintf('/uploads/vendor/%02d/logo.png', $index),
                bannerPath: sprintf('/uploads/vendor/%02d/cover.png', $index),
                gallery: [
                    sprintf('/uploads/vendor/%02d/gallery-1.png', $index),
                    sprintf('/uploads/vendor/%02d/gallery-2.png', $index),
                ],
            );
            $manager->persist($media);
            $manager->persist(new VendorProfileAvatarEntity($vendor, sprintf('/uploads/vendor/%02d/logo.png', $index)));
            $manager->persist(new VendorProfileCoverEntity($vendor, sprintf('/uploads/vendor/%02d/cover.png', $index)));
            $manager->persist(new VendorMediaAttachmentEntity($media, 'gallery', sprintf('/uploads/vendor/%02d/gallery-1.png', $index), 0));
            $manager->persist(new VendorMediaAttachmentEntity($media, 'gallery', sprintf('/uploads/vendor/%02d/gallery-2.png', $index), 1));

            $document = new VendorDocumentEntity($vendor, 'kyc', sprintf('/docs/vendor/%02d/kyc.pdf', $index));
            $document->assignMetadata(null, 2000 + $index);
            $manager->persist($document);
            $manager->persist(new VendorDocumentAttachmentEntity($document, sprintf('/docs/vendor/%02d/kyc.pdf', $index)));

            $payment = new VendorPaymentEntity($vendor, 'stripe', 'card', ['source' => 'demo_fixture']);
            $payment->update(
                externalPaymentId: sprintf('pm_demo_%02d', $index),
                label: sprintf('Primary Card %02d', $index),
                status: 'active',
                isDefault: true,
                meta: ['last4' => (string) (4240 + $index)],
            );
            $manager->persist($payment);

            $commission = new VendorCommissionEntity($vendor, 'marketplace', 'debit', (string) (7 + $index));
            $manager->persist($commission);
            $manager->persist(new VendorCommissionHistoryEntity(
                vendor: $vendor,
                commission: $commission,
                previousRatePercent: null,
                newRatePercent: $commission->getRatePercent(),
                changedByUserId: 3000 + $index,
                reason: 'fixture_bootstrap',
            ));

            $conversation = new VendorConversationEntity($vendor, ['source' => 'demo_fixture']);
            $conversation->update(
                subject: sprintf('Support thread %02d', $index),
                channel: 0 === $index % 2 ? 'email' : 'chat',
                counterpartyType: 'customer',
                counterpartyId: sprintf('customer-%02d', $index),
                counterpartyName: sprintf('Customer %02d', $index),
                status: 0 === $index % 4 ? 'closed' : 'open',
                meta: ['priority' => 0 === $index % 2 ? 'high' : 'normal'],
            );
            $manager->persist($conversation);
            $manager->persist(new VendorConversationMessageEntity($conversation, 'inbound', sprintf('Hello vendor %02d', $index), null, sprintf('msg-in-%02d', $index)));
            $manager->persist(new VendorConversationMessageEntity($conversation, 'outbound', sprintf('Reply from vendor %02d', $index), $vendor, sprintf('msg-out-%02d', $index)));

            $shipment = new VendorShipmentEntity($vendor, ['source' => 'demo_fixture']);
            $shipment->update(
                externalShipmentId: sprintf('shp-%02d', $index),
                carrierCode: 0 === $index % 2 ? 'ups' : 'fedex',
                methodCode: 0 === $index % 2 ? 'ground' : 'express',
                trackingNumber: sprintf('TRK%06d', $index),
                status: 0 === $index % 3 ? 'delivered' : 'shipped',
                meta: ['source' => 'demo_fixture'],
            );
            $manager->persist($shipment);

            $manager->persist(new VendorGroupEntity($vendor, sprintf('grp-%02d', $index), sprintf('Vendor Group %02d', $index), ['source' => 'demo_fixture']));
            $manager->persist(new VendorCategoryEntity($vendor, sprintf('cat-%02d', $index), sprintf('Category %02d', $index), true));
            $manager->persist(new VendorFavouriteEntity($vendor, 'product', sprintf('product-%02d', $index), 'demo favourite'));

            $wishlist = new VendorWishlistEntity($vendor, sprintf('customer-%02d', $index), sprintf('Wishlist %02d', $index));
            $manager->persist($wishlist);
            $manager->persist(new VendorWishlistItemEntity($wishlist, 'product', sprintf('product-%02d', $index), 2, 'fixture item'));

            $codeStorage = new VendorCodeStorageEntity(
                vendor: $vendor,
                code: sprintf('OTP%04d', 1000 + $index),
                purpose: 'login',
                expiresAt: new \DateTimeImmutable('+15 minutes'),
            );
            $codeStorage->updateDelivery(sprintf('+1555111%04d', $index), true);
            $manager->persist($codeStorage);

            $manager->persist(new VendorRememberMeTokenEntity(
                vendor: $vendor,
                series: sprintf('series-%02d', $index),
                tokenValue: sprintf('token-value-%02d', $index),
                providerClass: 'App\\Security\\VendorRememberMeProvider',
                username: sprintf('vendor-user-%02d', $index),
            ));

            $manager->persist(new VendorCustomerOrderEntity(
                vendor: $vendor,
                externalOrderId: sprintf('order-ext-%02d', $index),
                status: 0 === $index % 2 ? 'paid' : 'pending',
                currency: 'USD',
                grossCents: 10000 + ($index * 500),
                netCents: 8500 + ($index * 450),
                meta: ['source' => 'demo_fixture'],
            ));

            $manager->persist(new VendorLogEntity($vendor, 'fixture.seeded', [
                'fixture' => self::class,
                'index' => $index,
                'ownerUserId' => 1000 + $index,
            ]));
        }

        $manager->flush();
    }
}
