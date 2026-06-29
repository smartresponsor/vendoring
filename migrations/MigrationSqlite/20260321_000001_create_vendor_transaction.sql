-- Migration: create vendor_transaction table (SQLite)
-- Generated for Vendoring RC vertical slice proofs

CREATE TABLE vendor_transaction (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id  VARCHAR(64) NOT NULL,
    order_id   VARCHAR(64) NOT NULL,
    project_id VARCHAR(64) DEFAULT NULL,
    amount NUMERIC(12,2) NOT NULL CHECK (amount > 0),
    status VARCHAR(64) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded')),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX uniq_vendor_transaction_vendor_order_project_nonnull
    ON vendor_transaction (vendor_id, order_id, project_id)
    WHERE project_id IS NOT NULL;

CREATE UNIQUE INDEX uniq_vendor_transaction_vendor_order_nullproject
    ON vendor_transaction (vendor_id, order_id)
    WHERE project_id IS NULL;
