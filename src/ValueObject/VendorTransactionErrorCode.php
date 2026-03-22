<?php

declare(strict_types=1);

namespace App\ValueObject;

final class VendorTransactionErrorCode
{
    public const DUPLICATE_TRANSACTION = 'duplicate_transaction';
    public const VENDOR_ID_REQUIRED = 'vendor_id_required';
    public const ORDER_ID_REQUIRED = 'order_id_required';
    public const PROJECT_ID_INVALID = 'project_id_invalid';
    public const AMOUNT_REQUIRED = 'amount_required';
    public const AMOUNT_NOT_NUMERIC = 'amount_not_numeric';
    public const AMOUNT_NOT_POSITIVE = 'amount_not_positive';
    public const STATUS_REQUIRED = 'status_required';
    public const INVALID_STATUS_TRANSITION = 'invalid_status_transition';
    public const NOT_FOUND = 'not_found';
    public const MALFORMED_JSON = 'malformed_json';

    private function __construct()
    {
    }
}
