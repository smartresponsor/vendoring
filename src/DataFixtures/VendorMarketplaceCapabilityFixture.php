<?php

declare(strict_types=1);

namespace App\Vendoring\DataFixtures;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorServiceEntity;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorServiceRepositoryInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class VendorMarketplaceCapabilityFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorServiceRepositoryInterface $serviceRepository,
    ) {
    }

    public static function getGroups(): array
    {
        return ['vendoring_marketplace_capabilities'];
    }

    public function load(ObjectManager $manager): void
    {
        $rows = $this->vendorRepository->findPublishedRetailServiceRows();

        /** @var array<string, array{vendorId: int, typePath: string, catalogCode: ?string, offeringIds: list<int>}> $capabilities */
        $capabilities = [];
        foreach ($rows as $row) {
            $vendorId = is_numeric($row['owner_id'] ?? null) ? (int) $row['owner_id'] : 0;
            $typePathValue = $row['type_path'] ?? null;
            $typePath = is_scalar($typePathValue) ? trim((string) $typePathValue) : '';
            $offeringId = is_numeric($row['id'] ?? null) ? (int) $row['id'] : 0;
            if ($vendorId < 1 || '' === $typePath || $offeringId < 1) {
                continue;
            }

            $key = $vendorId.':'.$typePath;
            $capabilities[$key] ??= [
                'vendorId' => $vendorId,
                'typePath' => $typePath,
                'catalogCode' => is_scalar($row['catalog_code'] ?? null) ? trim((string) $row['catalog_code']) : null,
                'offeringIds' => [],
            ];
            $capabilities[$key]['offeringIds'][] = $offeringId;
        }

        foreach ($capabilities as $capability) {
            $vendor = $this->vendorRepository->find($capability['vendorId']);
            if (!$vendor instanceof VendorEntity) {
                throw new \RuntimeException(sprintf('Published retail offering references missing vendor %d.', $capability['vendorId']));
            }

            $payload = [
                'source' => 'retailing',
                'catalogCode' => $capability['catalogCode'],
                'typePath' => $capability['typePath'],
                'offeringIds' => array_values(array_unique($capability['offeringIds'])),
            ];
            $code = 'retailing:'.$capability['typePath'];

            $service = $this->serviceRepository->findOneBy([
                'vendor' => $vendor,
                'categoryId' => $capability['typePath'],
            ]);
            if (!$service instanceof VendorServiceEntity) {
                $service = new VendorServiceEntity($vendor, $capability['typePath'], $code, $payload);
            } else {
                $service->update($code, $payload)->setStatus('active');
            }

            $this->serviceRepository->save($service, true);
        }
    }
}
