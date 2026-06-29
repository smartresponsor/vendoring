-- Migration: create vendor_transaction table (PostgreSQL)
-- Generated for Vendoring RC

CREATE TABLE vendor_transaction (
    id         SERIAL PRIMARY KEY,
    vendor_id  VARCHAR(64) NOT NULL,
    order_id   VARCHAR(64) NOT NULL,
    project_id VARCHAR(64) DEFAULT NULL,
    amount NUMERIC(12,2) NOT NULL CHECK (amount > 0),
    status VARCHAR(64) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'authorized', 'failed', 'cancelled', 'settled', 'refunded')),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE UNIQUE INDEX uniq_vendor_transaction_vendor_order_project_nonnull
    ON vendor_transaction (vendor_id, order_id, project_id)
    WHERE project_id IS NOT NULL;

CREATE UNIQUE INDEX uniq_vendor_transaction_vendor_order_nullproject
    ON vendor_transaction (vendor_id, order_id)
    WHERE project_id IS NULL;
