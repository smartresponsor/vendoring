<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Contract;

use App\Vendoring\Contract\VendoringIntegrationContract;
use App\Vendoring\ServiceInterface\Finance\VendorFinanceRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Integration\VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutProviderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionLifecycleServiceInterface;
use App\Vendoring\VendoringBundle;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for VendoringIntegrationContract DTO.
 *
 * Validates that the contract carries the expected canonical values
 * and that all referenced FQCNs exist and are interfaces/classes.
 */
final class VendoringIntegrationContractTest extends TestCase
{
    private VendoringIntegrationContract $contract;

    protected function setUp(): void
    {
        $this->contract = new VendoringIntegrationContract(
            owns: 'vendor_lifecycle',
            subjectPrefix: 'vendoring:vendor:',
            permissionPrefix: 'vendoring.',
            routeMapPrefix: 'vendor',
            bundleClass: VendoringBundle::class,
            ownershipProjectionBuilderInterface: VendorOwnershipProjectionBuilderServiceInterface::class,
            transactionLifecycleInterface: VendorTransactionLifecycleServiceInterface::class,
            payoutProviderInterface: VendorPayoutProviderServiceInterface::class,
            statementInterface: VendorStatementServiceInterface::class,
            financeRuntimeProjectionBuilderInterface: VendorFinanceRuntimeProjectionBuilderServiceInterface::class,
            externalIntegrationRuntimeProjectionBuilderInterface: VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class,
            surfaces: [
                'ownership.projection' => VendorOwnershipProjectionBuilderServiceInterface::class,
                'transaction.lifecycle' => VendorTransactionLifecycleServiceInterface::class,
                'payout.transfer' => VendorPayoutProviderServiceInterface::class,
                'statement.build' => VendorStatementServiceInterface::class,
                'finance.runtime' => VendorFinanceRuntimeProjectionBuilderServiceInterface::class,
                'integration.runtime' => VendorExternalIntegrationRuntimeProjectionBuilderServiceInterface::class,
            ],
        );
    }

    public function testOwnershipDomainIsVendorLifecycle(): void
    {
        self::assertSame('vendor_lifecycle', $this->contract->owns);
    }

    public function testSubjectPrefixFollowsConvention(): void
    {
        self::assertSame('vendoring:vendor:', $this->contract->subjectPrefix);
    }

    public function testPermissionPrefixFollowsConvention(): void
    {
        self::assertSame('vendoring.', $this->contract->permissionPrefix);
    }

    public function testRouteMapPrefixIsVendor(): void
    {
        self::assertSame('vendor', $this->contract->routeMapPrefix);
    }

    public function testBundleClassExists(): void
    {
        self::assertTrue(
            class_exists($this->contract->bundleClass),
            sprintf('Bundle class "%s" does not exist.', $this->contract->bundleClass),
        );
        self::assertSame(VendoringBundle::class, $this->contract->bundleClass);
    }

    public function testAllInterfaceFqcnsExist(): void
    {
        $interfaces = [
            'ownershipProjectionBuilderInterface' => $this->contract->ownershipProjectionBuilderInterface,
            'transactionLifecycleInterface' => $this->contract->transactionLifecycleInterface,
            'payoutProviderInterface' => $this->contract->payoutProviderInterface,
            'statementInterface' => $this->contract->statementInterface,
            'financeRuntimeProjectionBuilderInterface' => $this->contract->financeRuntimeProjectionBuilderInterface,
            'externalIntegrationRuntimeProjectionBuilderInterface' => $this->contract->externalIntegrationRuntimeProjectionBuilderInterface,
        ];

        foreach ($interfaces as $field => $fqcn) {
            self::assertTrue(
                interface_exists($fqcn) || class_exists($fqcn),
                sprintf('Contract field "%s" references non-existent FQCN "%s".', $field, $fqcn),
            );
        }
    }

    public function testSurfacesMapIsNonEmpty(): void
    {
        self::assertNotEmpty($this->contract->surfaces);
    }

    public function testSurfacesMapContainsExpectedKeys(): void
    {
        $expected = [
            'ownership.projection',
            'transaction.lifecycle',
            'payout.transfer',
            'statement.build',
            'finance.runtime',
            'integration.runtime',
        ];

        foreach ($expected as $key) {
            self::assertArrayHasKey(
                $key,
                $this->contract->surfaces,
                sprintf('Surface key "%s" missing from contract surfaces map.', $key),
            );
        }
    }

    public function testSurfaceFqcnsAllExist(): void
    {
        foreach ($this->contract->surfaces as $slug => $fqcn) {
            self::assertTrue(
                interface_exists($fqcn) || class_exists($fqcn),
                sprintf('Surface "%s" references non-existent FQCN "%s".', $slug, $fqcn),
            );
        }
    }

    public function testContractIsReadonly(): void
    {
        $reflection = new \ReflectionClass(VendoringIntegrationContract::class);
        self::assertTrue($reflection->isReadOnly(), 'VendoringIntegrationContract must be declared readonly.');
    }
}
