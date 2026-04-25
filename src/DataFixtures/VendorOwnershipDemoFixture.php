<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorBilling;
use App\Vendoring\Entity\VendorCategory;
use App\Vendoring\Entity\VendorCodeStorage;
use App\Vendoring\Entity\VendorCommission;
use App\Vendoring\Entity\VendorCommissionHistory;
use App\Vendoring\Entity\VendorConversation;
use App\Vendoring\Entity\VendorConversationMessage;
use App\Vendoring\Entity\VendorCustomerOrder;
use App\Vendoring\Entity\VendorDocument;
use App\Vendoring\Entity\VendorDocumentAttachment;
use App\Vendoring\Entity\VendorFavourite;
use App\Vendoring\Entity\VendorGroup;
use App\Vendoring\Entity\VendorIban;
use App\Vendoring\Entity\VendorLog;
use App\Vendoring\Entity\VendorMedia;
use App\Vendoring\Entity\VendorMediaAttachment;
use App\Vendoring\Entity\VendorPayment;
use App\Vendoring\Entity\VendorProfile;
use App\Vendoring\Entity\VendorProfileAvatar;
use App\Vendoring\Entity\VendorProfileCover;
use App\Vendoring\Entity\VendorRememberMeToken;
use App\Vendoring\Entity\VendorShipment;
use App\Vendoring\Entity\VendorWishlist;
use App\Vendoring\Entity\VendorWishlistItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class VendorOwnershipDemoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($index = 1; $index <= 6; ++$index) {
            $vendor = new Vendor(sprintf('Vendor Demo %02d', $index), 1000 + $index);
            if (0 !== $index % 3) {
                $vendor->activate();
            }

            $manager->persist($vendor);

            $profile = new VendorProfile($vendor);
            $profile->updateProfile(
                displayName: sprintf('Vendor Demo %02d LLC', $index),
                about: $faker->sentence(10),
                website: sprintf('https://vendor-demo-%02d.example.test', $index),
                socials: ['instagram' => sprintf('@vendor_demo_%02d', $index)],
                seoTitle: sprintf('Vendor Demo %02d', $index),
                seoDescription: $faker->sentence(12),
            );
            $manager->persist($profile);

            $billing = new VendorBilling($vendor);
            $billing->update(
                iban: sprintf('DE893704004405320130%02d', $index),
                swift: 'DEUTDEFF',
                payoutMethod: 0 === $index % 2 ? 'bank' : 'wire',
                billingEmail: sprintf('billing+%02d@example.test', $index),
            );
            $manager->persist($billing);
            $manager->persist(new VendorIban($vendor, sprintf('DE893704004405320130%02d', $index), 'DEUTDEFF'));

            $media = new VendorMedia($vendor);
            $media->update(
                logoPath: sprintf('/uploads/vendor/%02d/logo.png', $index),
                bannerPath: sprintf('/uploads/vendor/%02d/cover.png', $index),
                gallery: [
                    sprintf('/uploads/vendor/%02d/gallery-1.png', $index),
                    sprintf('/uploads/vendor/%02d/gallery-2.png', $index),
                ],
            );
            $manager->persist($media);
            $manager->persist(new VendorProfileAvatar($vendor, sprintf('/uploads/vendor/%02d/logo.png', $index)));
            $manager->persist(new VendorProfileCover($vendor, sprintf('/uploads/vendor/%02d/cover.png', $index)));
            $manager->persist(new VendorMediaAttachment($media, 'gallery', sprintf('/uploads/vendor/%02d/gallery-1.png', $index), 0));
            $manager->persist(new VendorMediaAttachment($media, 'gallery', sprintf('/uploads/vendor/%02d/gallery-2.png', $index), 1));

            $document = new VendorDocument($vendor, 'kyc', sprintf('/docs/vendor/%02d/kyc.pdf', $index));
            $document->assignMetadata(null, 2000 + $index);
            $manager->persist($document);
            $manager->persist(new VendorDocumentAttachment($document, sprintf('/docs/vendor/%02d/kyc.pdf', $index)));

            $payment = new VendorPayment($vendor, 'stripe', 'card', ['source' => 'demo_fixture']);
            $payment->update(
                externalPaymentId: sprintf('pm_demo_%02d', $index),
                label: sprintf('Primary Card %02d', $index),
                status: 'active',
                isDefault: true,
                meta: ['last4' => (string) (4240 + $index)],
            );
            $manager->persist($payment);

            $commission = new VendorCommission($vendor, 'marketplace', 'debit', (string) (7 + $index));
            $manager->persist($commission);
            $manager->persist(new VendorCommissionHistory(
                vendor: $vendor,
                commission: $commission,
                previousRatePercent: null,
                newRatePercent: $commission->getRatePercent(),
                changedByUserId: 3000 + $index,
                reason: 'fixture_bootstrap',
            ));

            $conversation = new VendorConversation($vendor, ['source' => 'demo_fixture']);
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
            $manager->persist(new VendorConversationMessage($conversation, 'inbound', sprintf('Hello vendor %02d', $index), null, sprintf('msg-in-%02d', $index)));
            $manager->persist(new VendorConversationMessage($conversation, 'outbound', sprintf('Reply from vendor %02d', $index), $vendor, sprintf('msg-out-%02d', $index)));

            $shipment = new VendorShipment($vendor, ['source' => 'demo_fixture']);
            $shipment->update(
                externalShipmentId: sprintf('shp-%02d', $index),
                carrierCode: 0 === $index % 2 ? 'ups' : 'fedex',
                methodCode: 0 === $index % 2 ? 'ground' : 'express',
                trackingNumber: sprintf('TRK%06d', $index),
                status: 0 === $index % 3 ? 'delivered' : 'shipped',
                meta: ['source' => 'demo_fixture'],
            );
            $manager->persist($shipment);

            $manager->persist(new VendorGroup($vendor, sprintf('grp-%02d', $index), sprintf('Vendor Group %02d', $index), ['source' => 'demo_fixture']));
            $manager->persist(new VendorCategory($vendor, sprintf('cat-%02d', $index), sprintf('Category %02d', $index), true));
            $manager->persist(new VendorFavourite($vendor, 'product', sprintf('product-%02d', $index), 'demo favourite'));

            $wishlist = new VendorWishlist($vendor, sprintf('customer-%02d', $index), sprintf('Wishlist %02d', $index));
            $manager->persist($wishlist);
            $manager->persist(new VendorWishlistItem($wishlist, 'product', sprintf('product-%02d', $index), 2, 'fixture item'));

            $codeStorage = new VendorCodeStorage(
                vendor: $vendor,
                code: sprintf('OTP%04d', 1000 + $index),
                purpose: 'login',
                expiresAt: new \DateTimeImmutable('+15 minutes'),
            );
            $codeStorage->updateDelivery(sprintf('+1555111%04d', $index), true);
            $manager->persist($codeStorage);

            $manager->persist(new VendorRememberMeToken(
                vendor: $vendor,
                series: sprintf('series-%02d', $index),
                tokenValue: sprintf('token-value-%02d', $index),
                providerClass: 'App\\Security\\VendorRememberMeProvider',
                username: sprintf('vendor-user-%02d', $index),
            ));

            $manager->persist(new VendorCustomerOrder(
                vendor: $vendor,
                externalOrderId: sprintf('order-ext-%02d', $index),
                status: 0 === $index % 2 ? 'paid' : 'pending',
                currency: 'USD',
                grossCents: 10000 + ($index * 500),
                netCents: 8500 + ($index * 450),
                meta: ['source' => 'demo_fixture'],
            ));

            $manager->persist(new VendorLog($vendor, 'fixture.seeded', [
                'fixture' => self::class,
                'index' => $index,
                'ownerUserId' => 1000 + $index,
            ]));
        }

        $manager->flush();
    }
}
