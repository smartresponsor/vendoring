<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\VendorBilling;
use App\Entity\VendorDocument;
use App\Entity\VendorMedia;
use App\Entity\VendorProfile;
use PHPUnit\Framework\TestCase;

final class VendorEntityServiceCompatibilityTest extends TestCase
{
    /**
     * @param class-string $className
     * @param list<string> $properties
     */
    private function assertEntityDefinesProperties(string $className, array $properties): void
    {
        self::assertTrue(class_exists($className) || interface_exists($className));
        /** @var class-string $className */
        $reflection = new \ReflectionClass($className);

        foreach ($properties as $property) {
            self::assertTrue(
                $reflection->hasProperty($property),
                sprintf('%s must define $%s for current service reflection flow.', $className, $property),
            );
        }
    }

    public function testProfileEntityDefinesReflectionBackedProperties(): void
    {
        $this->assertEntityDefinesProperties(VendorProfile::class, [
            'displayName',
            'about',
            'website',
            'socials',
            'seoTitle',
            'seoDescription',
        ]);
    }

    public function testBillingEntityDefinesReflectionBackedProperties(): void
    {
        $this->assertEntityDefinesProperties(VendorBilling::class, [
            'iban',
            'swift',
            'payoutMethod',
            'billingEmail',
        ]);
    }

    public function testMediaEntityDefinesReflectionBackedProperties(): void
    {
        $this->assertEntityDefinesProperties(VendorMedia::class, [
            'logoPath',
            'bannerPath',
            'gallery',
        ]);
    }

    public function testDocumentEntityDefinesReflectionBackedProperties(): void
    {
        $this->assertEntityDefinesProperties(VendorDocument::class, [
            'expiresAt',
            'uploaderId',
        ]);
    }
}
