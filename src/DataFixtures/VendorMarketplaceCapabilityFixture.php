<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorServiceEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorMarketplaceCapabilityFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['vendoring_marketplace_capabilities'];
    }

    public function load(ObjectManager $manager): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            return;
        }

        $rows = $manager->getConnection()->fetchAllAssociative(
            "SELECT id, owner_id, category_id, catalog_code FROM retail WHERE kind = 'service' AND owner_type = 'vendor' AND object_status = 'published' ORDER BY owner_id, category_id, id",
        );

        /** @var array<string, array{vendorId: int, categoryId: string, catalogCode: ?string, offeringIds: list<int>}> $capabilities */
        $capabilities = [];
        foreach ($rows as $row) {
            $vendorId = is_numeric($row['owner_id'] ?? null) ? (int) $row['owner_id'] : 0;
            $categoryId = trim((string) ($row['category_id'] ?? ''));
            $offeringId = is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0;
            if ($vendorId < 1 || '' === $categoryId || $offeringId < 1) {
                continue;
            }

            $key = $vendorId.':'.$categoryId;
            $capabilities[$key] ??= [
                'vendorId' => $vendorId,
                'categoryId' => $categoryId,
                'catalogCode' => null === ($row['catalog_code'] ?? null) ? null : trim((string) $row['catalog_code']),
                'offeringIds' => [],
            ];
            $capabilities[$key]['offeringIds'][] = $offeringId;
        }

        foreach ($capabilities as $capability) {
            $vendor = $manager->getRepository(VendorEntity::class)->find($capability['vendorId']);
            if (!$vendor instanceof VendorEntity) {
                throw new \RuntimeException(sprintf('Published retail offering references missing vendor %d.', $capability['vendorId']));
            }

            $payload = [
                'source' => 'retailing',
                'catalogCode' => $capability['catalogCode'],
                'offeringIds' => array_values(array_unique($capability['offeringIds'])),
            ];
            $code = 'services:'.$capability['categoryId'];

            $service = $manager->getRepository(VendorServiceEntity::class)->findOneBy([
                'vendor' => $vendor,
                'categoryId' => $capability['categoryId'],
            ]);
            if (!$service instanceof VendorServiceEntity) {
                $service = new VendorServiceEntity($vendor, $capability['categoryId'], $code, $payload);
                $manager->persist($service);
            } else {
                $service->update($code, $payload)->setStatus('active');
            }
        }

        $manager->flush();
    }
}
