<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Service\Transaction\VendorTransactionInputResolverService;
use App\Vendoring\ValueObject\VendorTransactionErrorCodeValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class VendorTransactionInputResolverServiceTest extends TestCase
{
    public function testResolveCreateDataTrimsRequiredFieldsAndNormalizesBlankProjectIdToNull(): void
    {
        $request = Request::create(
            '/api/vendor-transactions',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'vendorId' => ' vendor-1 ',
                'orderId' => ' order-1 ',
                'projectId' => '   ',
                'amount' => ' 10.50 ',
            ], JSON_THROW_ON_ERROR),
        );

        $data = (new VendorTransactionInputResolverService())->resolveCreateData($request);

        self::assertSame('vendor-1', $data->vendorId);
        self::assertSame('order-1', $data->orderId);
        self::assertNull($data->projectId);
        self::assertSame('10.50', $data->amount);
    }

    public function testResolveCreateDataRejectsBlankRequiredFieldAfterTrim(): void
    {
        $request = Request::create(
            '/api/vendor-transactions',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'vendorId' => 'vendor-1',
                'orderId' => '   ',
                'amount' => '10.00',
            ], JSON_THROW_ON_ERROR),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(VendorTransactionErrorCodeValueObject::ORDER_ID_REQUIRED);

        (new VendorTransactionInputResolverService())->resolveCreateData($request);
    }

    public function testResolveStatusTrimsIncomingStatus(): void
    {
        $request = Request::create(
            '/api/vendor-transactions/vendor/vendor-1/1/status',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['status' => ' settled '], JSON_THROW_ON_ERROR),
        );

        $status = (new VendorTransactionInputResolverService())->resolveStatus($request);

        self::assertSame('settled', $status);
    }

    public function testNormalizeErrorCodeMapsPrefixedTransitionErrorsAndUnknownMessages(): void
    {
        $service = new VendorTransactionInputResolverService();

        self::assertSame(
            VendorTransactionErrorCodeValueObject::INVALID_STATUS_TRANSITION,
            $service->normalizeErrorCode('invalid_status_transition:pending->refunded'),
        );
        self::assertSame('transaction_validation_error', $service->normalizeErrorCode('unexpected_failure'));
    }
}
