<?php

declare(strict_types=1);

namespace App\ValueObject;

final class VendorTransactionErrorCode
{
    public const string DUPLICATE_TRANSACTION = 'duplicate_transaction';
    public const string VENDOR_ID_REQUIRED = 'vendor_id_required';
    public const string ORDER_ID_REQUIRED = 'order_id_required';
    public const string PROJECT_ID_INVALID = 'project_id_invalid';
    public const string AMOUNT_REQUIRED = 'amount_required';
    public const string AMOUNT_NOT_NUMERIC = 'amount_not_numeric';
    public const string AMOUNT_NOT_POSITIVE = 'amount_not_positive';
    public const string STATUS_REQUIRED = 'status_required';
    public const string INVALID_STATUS_TRANSITION = 'invalid_status_transition';
    public const string NOT_FOUND = 'not_found';
    public const string MALFORMED_JSON = 'malformed_json';

    private function __construct()
    {
    }
}
