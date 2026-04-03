<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\VendorTransaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class VendorTransactionDemoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');
        $vendorIds = ['demo-vendor-a', 'demo-vendor-b', 'demo-vendor-c'];
        $statuses = ['pending', 'authorized', 'captured', 'refunded'];

        for ($index = 1; $index <= 30; ++$index) {
            $vendorId = self::stringValue($faker->randomElement($vendorIds));
            $transaction = new VendorTransaction(
                vendorId: $vendorId,
                orderId: sprintf('order-%s', $faker->unique()->numerify('#####')),
                projectId: $faker->boolean(75) ? sprintf('project-%02d', $faker->numberBetween(1, 8)) : null,
                amount: (string) $faker->randomFloat(2, 10, 1500),
            );

            $transaction->setStatus(self::stringValue($faker->randomElement($statuses)));
            $manager->persist($transaction);
        }

        $manager->flush();
    }

    private static function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
