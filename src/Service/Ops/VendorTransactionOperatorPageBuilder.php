<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Ops;

use App\Entity\Vendor\VendorTransaction;
use App\ServiceInterface\Ops\VendorTransactionOperatorPageBuilderInterface;

final class VendorTransactionOperatorPageBuilder implements VendorTransactionOperatorPageBuilderInterface
{
    /**
     * @param list<VendorTransaction> $transactions
     */
    public function renderIndex(string $vendorId, array $transactions, ?string $flashMessage = null, ?string $errorMessage = null): string
    {
        $vendorIdEscaped = $this->escape($vendorId);
        $flashMarkup = null === $flashMessage ? '' : '<div class="alert alert-success" role="alert">'.$this->escape($flashMessage).'</div>';
        $errorMarkup = null === $errorMessage ? '' : '<div class="alert alert-danger" role="alert">'.$this->escape($errorMessage).'</div>';

        $rows = '';
        foreach ($transactions as $transaction) {
            $rows .= sprintf(
                <<<'HTML'
<tr>
    <td>%d</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>%s</td>
    <td>
        <form method="post" action="/ops/vendor-transactions/%s/%d/status" class="row g-2 align-items-center">
            <div class="col-md-8">
                <label class="visually-hidden" for="status-%d">Status</label>
                <select id="status-%d" name="status" class="form-select form-select-sm">
                    %s
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">Update</button>
            </div>
        </form>
    </td>
</tr>
HTML,
                $transaction->getId(),
                $this->escape($transaction->getOrderId()),
                $this->escape($transaction->getProjectId() ?? '—'),
                $this->escape($transaction->getAmount()),
                $this->escape($transaction->getStatus()),
                $vendorIdEscaped,
                $transaction->getId(),
                $transaction->getId(),
                $transaction->getId(),
                $this->renderStatusOptions($transaction->getStatus()),
            );
        }

        if ('' === $rows) {
            $rows = '<tr><td colspan="6" class="text-center text-muted">No transactions found for this vendor yet.</td></tr>';
        }

        return sprintf(
            <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vendor transaction operator surface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Vendor transaction operator surface</h1>
            <p class="text-muted mb-0">Minimal server-rendered runtime slice for vendor <strong>%s</strong>.</p>
        </div>
        <a class="btn btn-outline-secondary" href="/ops/vendor-transactions/%s">Refresh</a>
    </div>

    %s
    %s

    <div class="card shadow-sm mb-4">
        <div class="card-header">Create transaction</div>
        <div class="card-body">
            <form method="post" action="/ops/vendor-transactions/%s/create" class="row g-3">
                <div class="col-md-3">
                    <label for="vendorId" class="form-label">Vendor ID</label>
                    <input id="vendorId" name="vendorId" class="form-control" value="%s" required>
                </div>
                <div class="col-md-3">
                    <label for="orderId" class="form-label">Order ID</label>
                    <input id="orderId" name="orderId" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label for="projectId" class="form-label">Project ID</label>
                    <input id="projectId" name="projectId" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="amount" class="form-label">Amount</label>
                    <input id="amount" name="amount" class="form-control" inputmode="decimal" required>
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">Transactions</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Project</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="w-25">Operator action</th>
                    </tr>
                    </thead>
                    <tbody>
                    %s
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
HTML,
            $vendorIdEscaped,
            $vendorIdEscaped,
            $flashMarkup,
            $errorMarkup,
            $vendorIdEscaped,
            $vendorIdEscaped,
            $rows,
        );
    }

    private function renderStatusOptions(string $currentStatus): string
    {
        $statuses = ['pending', 'authorized', 'captured', 'failed', 'refunded'];
        $options = '';

        foreach ($statuses as $status) {
            $selected = $status === $currentStatus ? ' selected' : '';
            $options .= sprintf('<option value="%s"%s>%s</option>', $this->escape($status), $selected, $this->escape(ucfirst($status)));
        }

        return $options;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
