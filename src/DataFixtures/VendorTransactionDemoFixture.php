<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VendorTransaction;
use App\ValueObject\VendorTransactionStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class VendorTransactionDemoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');
        $vendorIds = ['demo-vendor-a', 'demo-vendor-b', 'demo-vendor-c'];
        $statuses = [
            VendorTransactionStatus::PENDING,
            VendorTransactionStatus::AUTHORIZED,
            VendorTransactionStatus::FAILED,
            VendorTransactionStatus::SETTLED,
            VendorTransactionStatus::REFUNDED,
        ];

        for ($index = 1; $index <= 30; ++$index) {
            /** @var string $vendorId */
            $vendorId = $faker->randomElement($vendorIds);
            $transaction = new VendorTransaction(
                vendorId: $vendorId,
                orderId: sprintf('order-%s', $faker->unique()->numerify('#####')),
                projectId: $faker->boolean(75) ? sprintf('project-%02d', $faker->numberBetween(1, 8)) : null,
                amount: (string) $faker->randomFloat(2, 10, 1500),
            );

            /** @var string $status */
            $status = $faker->randomElement($statuses);
            $transaction->setStatus($status);
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
