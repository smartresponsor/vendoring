<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$outDir = $root . '/build/docs';
if (!is_dir($outDir) && !mkdir($outDir, 0777, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Failed to create docs output directory.\n");
    exit(1);
}

$spec = [
    'openapi' => '3.1.0',
    'info' => [
        'title' => 'Vendoring API',
        'version' => '0.5.0-rc-docs',
        'description' =>
            'Release-candidate API surface for the Vendoring component. '
            .'Generated from repository-owned metadata and DocBlock-aligned route contracts.',
    ],
    'servers' => [
        ['url' => '/'],
    ],
    'tags' => [
        ['name' => 'Vendor Transactions', 'description' => 'Transaction write and read surfaces for vendors.'],
        [
            'name' => 'Operator Surface',
            'description' => 'Minimal server-rendered operator/admin seam used for RC evidence.',
        ],
    ],
    'paths' => [
        '/api/vendor-transactions' => [
            'post' => [
                'tags' => ['Vendor Transactions'],
                'summary' => 'Create a vendor transaction',
                'operationId' => 'createVendorTransaction',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/VendorTransactionCreateRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Transaction created.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/VendorTransactionCreateResponse'],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Malformed JSON payload.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                                'examples' => [
                                    'malformed_json' => ['value' => ['error' => 'malformed_json']],
                                ],
                            ],
                        ],
                    ],
                    '409' => [
                        'description' => 'Duplicate transaction.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Validation failure.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/vendor-transactions/vendor/{vendorId}' => [
            'get' => [
                'tags' => ['Vendor Transactions'],
                'summary' => 'List transactions by vendor',
                'operationId' => 'listVendorTransactionsByVendor',
                'parameters' => [[
                    'name' => 'vendorId',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                ]],
                'responses' => [
                    '200' => [
                        'description' => 'Transaction collection.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/VendorTransactionCollectionResponse'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/vendor-transactions/vendor/{vendorId}/{id}/status' => [
            'post' => [
                'tags' => ['Vendor Transactions'],
                'summary' => 'Update transaction status',
                'operationId' => 'updateVendorTransactionStatus',
                'parameters' => [
                    [
                        'name' => 'vendorId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/VendorTransactionStatusRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Transaction status updated.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/VendorTransactionStatusResponse'],
                            ],
                        ],
                    ],
                    '400' => [
                        'description' => 'Malformed JSON payload.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                            ],
                        ],
                    ],
                    '404' => [
                        'description' => 'Transaction not found.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                            ],
                        ],
                    ],
                    '422' => [
                        'description' => 'Status validation failure.',
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/ErrorResponse'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/ops/vendor-transactions/{vendorId}' => [
            'get' => [
                'tags' => ['Operator Surface'],
                'summary' => 'Render operator transaction page',
                'operationId' => 'renderVendorTransactionOperatorPage',
                'parameters' => [[
                    'name' => 'vendorId',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                ]],
                'responses' => [
                    '200' => [
                        'description' => 'HTML operator page.',
                        'content' => ['text/html' => ['schema' => ['type' => 'string']]],
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'schemas' => [
            'VendorTransactionCreateRequest' => [
                'type' => 'object',
                'required' => ['vendorId', 'orderId', 'amount'],
                'properties' => [
                    'vendorId' => ['type' => 'string'],
                    'orderId' => ['type' => 'string'],
                    'projectId' => ['type' => ['string', 'null']],
                    'amount' => ['type' => 'string', 'description' => 'Decimal amount represented as a string.'],
                ],
            ],
            'VendorTransactionResource' => [
                'type' => 'object',
                'required' => ['id', 'vendorId', 'orderId', 'amount', 'status'],
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'vendorId' => ['type' => 'string'],
                    'orderId' => ['type' => 'string'],
                    'projectId' => ['type' => ['string', 'null']],
                    'amount' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                ],
            ],
            'VendorTransactionCreateResponse' => [
                'type' => 'object',
                'required' => ['id', 'status'],
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'status' => ['type' => 'string'],
                ],
            ],
            'VendorTransactionCollectionResponse' => [
                'type' => 'object',
                'required' => ['data'],
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/VendorTransactionResource'],
                    ],
                ],
            ],
            'VendorTransactionStatusRequest' => [
                'type' => 'object',
                'required' => ['status'],
                'properties' => [
                    'status' => ['type' => 'string'],
                ],
            ],
            'VendorTransactionStatusResponse' => [
                'type' => 'object',
                'required' => ['id', 'status'],
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'status' => ['type' => 'string'],
                ],
            ],
            'ErrorResponse' => [
                'type' => 'object',
                'required' => ['error'],
                'properties' => [
                    'error' => [
                        'type' => 'string',
                        'enum' => [
                            'duplicate_transaction',
                            'vendor_id_required',
                            'order_id_required',
                            'amount_required',
                            'amount_not_numeric',
                            'amount_not_positive',
                            'status_required',
                            'invalid_status_transition',
                            'not_found',
                            'malformed_json',
                            'transaction_validation_error',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

file_put_contents($outDir . '/openapi.json', json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

function yamlEncode(mixed $value, int $indent = 0): string
{
    $spaces = str_repeat('  ', $indent);

    if (is_array($value)) {
        $isList = array_keys($value) === range(0, count($value) - 1);
        $lines = [];

        foreach ($value as $key => $item) {
            if ($isList) {
                if (is_array($item)) {
                    $lines[] = $spaces . '-';
                    $lines[] = yamlEncode($item, $indent + 1);
                } else {
                    $lines[] = $spaces . '- ' . yamlScalar($item);
                }

                continue;
            }

            if (is_array($item)) {
                $lines[] = $spaces . $key . ':';
                $lines[] = yamlEncode($item, $indent + 1);
            } else {
                $lines[] = $spaces . $key . ': ' . yamlScalar($item);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    return $spaces . yamlScalar($value);
}

function yamlScalar(mixed $value): string
{
    if (is_string($value)) {
        if (
            $value === ''
            || preg_match('/[:\[\]{},&*#?|<>=%@`]/', $value) === 1
            || str_contains($value, ' ')
            || str_contains($value, '/')
        ) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if ($value === null) {
        return 'null';
    }

    return (string) $value;
}

file_put_contents($outDir . '/openapi.yaml', yamlEncode($spec) . PHP_EOL);
echo "Generated build/docs/openapi.json and build/docs/openapi.yaml\n";
