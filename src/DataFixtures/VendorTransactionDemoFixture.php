<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VendorTransactionDemoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $vendorIds = ['demo-vendor-a', 'demo-vendor-b', 'demo-vendor-c'];
        $statuses = ['pending', 'authorized', 'captured', 'refunded'];

        for ($index = 1; $index <= 30; ++$index) {
            $vendorId = $vendorIds[$index % \count($vendorIds)];
            $transaction = new VendorTransactionEntity(
                vendorId: $vendorId,
                orderId: sprintf('order-%05d', 10000 + $index),
                projectId: 0 !== $index % 4 ? sprintf('project-%02d', ($index % 8) + 1) : null,
                amount: number_format(10 + ($index * 47.35), 2, '.', ''),
            );

            $transaction->setStatus($statuses[$index % \count($statuses)]);
            $manager->persist($transaction);
        }

        $manager->flush();
    }
}
