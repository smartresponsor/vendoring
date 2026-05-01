<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Entity;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\EntityInterface\Vendor\VendorTransactionEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

final class VendorTransactionDoctrineMappingTest extends TestCase
{
    public function testEntityDeclaresDoctrineMappingAndCanonicalContract(): void
    {
        $reflection = new \ReflectionClass(VendorTransactionEntity::class);

        self::assertTrue($reflection->implementsInterface(VendorTransactionEntityInterface::class));

        $entityAttributes = $reflection->getAttributes(ORM\Entity::class);
        self::assertCount(1, $entityAttributes);
        self::assertSame(
            'App\Vendoring\\Repository\\VendorTransactionRepository',
            $entityAttributes[0]->getArguments()['repositoryClass'] ?? null,
        );

        $tableAttributes = $reflection->getAttributes(ORM\Table::class);
        self::assertCount(1, $tableAttributes);
        self::assertSame('vendor_transaction', $tableAttributes[0]->getArguments()['name'] ?? null);
    }

    public function testPersistentFieldsExposeExpectedDoctrineColumns(): void
    {
        $reflection = new \ReflectionClass(VendorTransactionEntity::class);

        $id = $reflection->getProperty('id');
        self::assertNotEmpty($id->getAttributes(ORM\Id::class));
        self::assertNotEmpty($id->getAttributes(ORM\GeneratedValue::class));
        self::assertSame('integer', $id->getAttributes(ORM\Column::class)[0]->getArguments()['type'] ?? null);

        $vendorId = $reflection->getProperty('vendorId')->getAttributes(ORM\Column::class)[0]->newInstance();
        self::assertSame('string', $vendorId->type);
        self::assertSame(64, $vendorId->length);

        $amount = $reflection->getProperty('amount')->getAttributes(ORM\Column::class)[0]->newInstance();
        self::assertSame('decimal', $amount->type);
        self::assertSame(12, $amount->precision);
        self::assertSame(2, $amount->scale);

        $createdAt = $reflection->getProperty('createdAt')->getAttributes(ORM\Column::class)[0]->newInstance();
        self::assertSame('datetime_immutable', $createdAt->type);
    }

    public function testRepositoryOrdersVendorTransactionsByNewestFirst(): void
    {
        $repositorySource = file_get_contents(__DIR__ . '/../../../src/Repository/Vendor/VendorTransactionRepository.php');
        self::assertIsString($repositorySource);
        self::assertStringContainsString("['createdAt' => 'DESC', 'id' => 'DESC']", $repositorySource);
    }
}
