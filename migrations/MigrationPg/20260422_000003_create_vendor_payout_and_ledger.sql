CREATE TABLE IF NOT EXISTS payouts (
    id UUID PRIMARY KEY,
    vendor_id VARCHAR(255) NOT NULL,
    currency VARCHAR(8) NOT NULL,
    gross_cents INT NOT NULL,
    fee_cents INT NOT NULL,
    net_cents INT NOT NULL,
    status VARCHAR(32) NOT NULL,
    created_at VARCHAR(19) NOT NULL,
    processed_at VARCHAR(19) DEFAULT NULL,
    meta JSONB NOT NULL DEFAULT '{}'::jsonb
);

CREATE INDEX IF NOT EXISTS idx_payouts_vendor ON payouts (vendor_id);
CREATE INDEX IF NOT EXISTS idx_payouts_status ON payouts (status);

CREATE TABLE IF NOT EXISTS payout_items (
    id UUID PRIMARY KEY,
    payout_id UUID NOT NULL,
    entry_id VARCHAR(64) NOT NULL,
    amount_cents INT NOT NULL,
    CONSTRAINT fk_payout_items_payout FOREIGN KEY (payout_id) REFERENCES payouts (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_payout_items_payout ON payout_items (payout_id);

CREATE TABLE IF NOT EXISTS payout_accounts (
    id UUID PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    vendor_id VARCHAR(255) NOT NULL,
    provider VARCHAR(64) NOT NULL,
    account_ref VARCHAR(255) NOT NULL,
    currency VARCHAR(8) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at VARCHAR(19) NOT NULL,
    CONSTRAINT uniq_payout_accounts_tenant_vendor UNIQUE (tenant_id, vendor_id)
);

CREATE INDEX IF NOT EXISTS idx_payout_accounts_vendor ON payout_accounts (vendor_id);

CREATE TABLE IF NOT EXISTS vendor_ledger_entries (
    id UUID PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    vendor_id VARCHAR(255) DEFAULT NULL,
    reference_type VARCHAR(64) NOT NULL,
    reference_id VARCHAR(64) NOT NULL,
    debit_account VARCHAR(64) NOT NULL,
    credit_account VARCHAR(64) NOT NULL,
    amount DOUBLE PRECISION NOT NULL,
    currency VARCHAR(8) NOT NULL,
    created_at VARCHAR(19) NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_vendor_ledger_ref ON vendor_ledger_entries (tenant_id, reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_vendor_ledger_vendor ON vendor_ledger_entries (vendor_id);
