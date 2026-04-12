<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\VendorTransaction;
use App\EntityInterface\VendorTransactionInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use PHPUnit\Framework\TestCase;

final class VendorTransactionDoctrineMappingTest extends TestCase
{
    public function testEntityDeclaresDoctrineMappingAndCanonicalContract(): void
    {
        $reflection = new \ReflectionClass(VendorTransaction::class);

        self::assertTrue($reflection->implementsInterface(VendorTransactionInterface::class));

        $entityAttributes = $reflection->getAttributes(Entity::class);
        self::assertCount(1, $entityAttributes);
        self::assertSame(
            'App\\Repository\\VendorTransactionRepository',
            $entityAttributes[0]->getArguments()['repositoryClass'] ?? null,
        );

        $tableAttributes = $reflection->getAttributes(Table::class);
        self::assertCount(1, $tableAttributes);
        self::assertSame('vendor_transaction', $tableAttributes[0]->getArguments()['name'] ?? null);
    }

    public function testPersistentFieldsExposeExpectedDoctrineColumns(): void
    {
        $reflection = new \ReflectionClass(VendorTransaction::class);

        $id = $reflection->getProperty('id');
        self::assertNotEmpty($id->getAttributes(Id::class));
        self::assertNotEmpty($id->getAttributes(GeneratedValue::class));
        self::assertSame('integer', $id->getAttributes(Column::class)[0]->getArguments()['type'] ?? null);

        $vendorId = $reflection->getProperty('vendorId')->getAttributes(Column::class)[0]->newInstance();
        self::assertSame('string', $vendorId->type);
        self::assertSame(64, $vendorId->length);

        $amount = $reflection->getProperty('amount')->getAttributes(Column::class)[0]->newInstance();
        self::assertSame('decimal', $amount->type);
        self::assertSame(12, $amount->precision);
        self::assertSame(2, $amount->scale);

        $createdAt = $reflection->getProperty('createdAt')->getAttributes(Column::class)[0]->newInstance();
        self::assertSame('datetime_immutable', $createdAt->type);
    }

    public function testRepositoryOrdersVendorTransactionsByNewestFirst(): void
    {
        $repositorySource = file_get_contents(__DIR__ . '/../../../src/Repository/VendorTransactionRepository.php');
        self::assertIsString($repositorySource);
        self::assertStringContainsString("['createdAt' => 'DESC', 'id' => 'DESC']", $repositorySource);
    }
}
