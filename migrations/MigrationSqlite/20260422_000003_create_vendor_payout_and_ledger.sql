CREATE TABLE IF NOT EXISTS payouts (
    id TEXT PRIMARY KEY,
    vendor_id TEXT NOT NULL,
    currency TEXT NOT NULL,
    gross_cents INTEGER NOT NULL,
    fee_cents INTEGER NOT NULL,
    net_cents INTEGER NOT NULL,
    status TEXT NOT NULL,
    created_at TEXT NOT NULL,
    processed_at TEXT DEFAULT NULL,
    meta TEXT NOT NULL DEFAULT '{}'
);

CREATE INDEX IF NOT EXISTS idx_payouts_vendor ON payouts (vendor_id);
CREATE INDEX IF NOT EXISTS idx_payouts_status ON payouts (status);

CREATE TABLE IF NOT EXISTS payout_items (
    id TEXT PRIMARY KEY,
    payout_id TEXT NOT NULL,
    entry_id TEXT NOT NULL,
    amount_cents INTEGER NOT NULL,
    FOREIGN KEY (payout_id) REFERENCES payouts (id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_payout_items_payout ON payout_items (payout_id);

CREATE TABLE IF NOT EXISTS payout_accounts (
    id TEXT PRIMARY KEY,
    tenant_id TEXT NOT NULL,
    vendor_id TEXT NOT NULL,
    provider TEXT NOT NULL,
    account_ref TEXT NOT NULL,
    currency TEXT NOT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL,
    UNIQUE (tenant_id, vendor_id)
);

CREATE INDEX IF NOT EXISTS idx_payout_accounts_vendor ON payout_accounts (vendor_id);

CREATE TABLE IF NOT EXISTS vendor_ledger_entries (
    id TEXT PRIMARY KEY,
    tenant_id TEXT NOT NULL,
    vendor_id TEXT DEFAULT NULL,
    reference_type TEXT NOT NULL,
    reference_id TEXT NOT NULL,
    debit_account TEXT NOT NULL,
    credit_account TEXT NOT NULL,
    amount REAL NOT NULL,
    currency TEXT NOT NULL,
    created_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_vendor_ledger_ref ON vendor_ledger_entries (tenant_id, reference_type, reference_id);
CREATE INDEX IF NOT EXISTS idx_vendor_ledger_vendor ON vendor_ledger_entries (vendor_id);
