CREATE TABLE vendor_payment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    provider_code VARCHAR(64) NOT NULL,
    method_code VARCHAR(64) NOT NULL,
    external_payment_id VARCHAR(128) DEFAULT NULL,
    label VARCHAR(255) DEFAULT NULL,
    status VARCHAR(32) NOT NULL,
    is_default INTEGER NOT NULL DEFAULT FALSE,
    meta JSON NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_payment_vendor_provider_method UNIQUE (vendor_id, provider_code, method_code)
);
CREATE INDEX idx_vendor_payment_vendor_status ON vendor_payment (vendor_id, status);

CREATE TABLE vendor_commission (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    direction VARCHAR(32) NOT NULL,
    rate_percent DECIMAL(6,2) NOT NULL,
    status VARCHAR(32) NOT NULL,
    effective_from DATETIME NOT NULL,
    effective_to DATETIME DEFAULT NULL,
    meta JSON NOT NULL
);
CREATE INDEX idx_vendor_commission_vendor_status ON vendor_commission (vendor_id, status);

CREATE TABLE vendor_commission_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    vendor_commission_id INTEGER DEFAULT NULL REFERENCES vendor_commission (id) ON DELETE SET NULL,
    changed_by_user_id INTEGER DEFAULT NULL,
    previous_rate_percent DECIMAL(6,2) DEFAULT NULL,
    new_rate_percent DECIMAL(6,2) NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    changed_at DATETIME NOT NULL
);
CREATE INDEX idx_vendor_commission_history_vendor_changed_at ON vendor_commission_history (vendor_id, changed_at);

CREATE TABLE vendor_conversation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    subject VARCHAR(255) DEFAULT NULL,
    channel VARCHAR(32) NOT NULL,
    counterparty_type VARCHAR(64) DEFAULT NULL,
    counterparty_id VARCHAR(128) DEFAULT NULL,
    counterparty_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(32) NOT NULL,
    meta JSON NOT NULL,
    opened_at DATETIME NOT NULL,
    closed_at DATETIME DEFAULT NULL
);
CREATE INDEX idx_vendor_conversation_vendor_status ON vendor_conversation (vendor_id, status);

CREATE TABLE vendor_conversation_message (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_conversation_id INTEGER NOT NULL REFERENCES vendor_conversation (id) ON DELETE CASCADE,
    sender_vendor_id INTEGER DEFAULT NULL REFERENCES vendor (id) ON DELETE SET NULL,
    direction VARCHAR(16) NOT NULL,
    body TEXT NOT NULL,
    external_message_id VARCHAR(128) DEFAULT NULL,
    meta JSON NOT NULL,
    sent_at DATETIME NOT NULL,
    read_at DATETIME DEFAULT NULL
);
CREATE INDEX idx_vendor_conversation_message_conversation_sent_at ON vendor_conversation_message (vendor_conversation_id, sent_at);

CREATE TABLE vendor_shipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    external_shipment_id VARCHAR(128) DEFAULT NULL,
    carrier_code VARCHAR(64) DEFAULT NULL,
    method_code VARCHAR(64) DEFAULT NULL,
    tracking_number VARCHAR(128) DEFAULT NULL,
    status VARCHAR(32) NOT NULL,
    meta JSON NOT NULL,
    shipped_at DATETIME DEFAULT NULL,
    delivered_at DATETIME DEFAULT NULL
);
CREATE INDEX idx_vendor_shipment_vendor_status ON vendor_shipment (vendor_id, status);

CREATE TABLE vendor_group (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    name VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL,
    meta JSON NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_group_vendor_code UNIQUE (vendor_id, code)
);

CREATE TABLE vendor_category (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    category_code VARCHAR(64) NOT NULL,
    category_name VARCHAR(255) DEFAULT NULL,
    is_primary INTEGER NOT NULL DEFAULT FALSE,
    assigned_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_category_vendor_category UNIQUE (vendor_id, category_code)
);

CREATE TABLE vendor_favourite (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    target_type VARCHAR(64) NOT NULL,
    target_id VARCHAR(128) NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_favourite_vendor_target UNIQUE (vendor_id, target_type, target_id)
);

CREATE TABLE vendor_wishlist (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    customer_reference VARCHAR(128) NOT NULL,
    name VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);
CREATE INDEX idx_vendor_wishlist_vendor_status ON vendor_wishlist (vendor_id, status);

CREATE TABLE vendor_wishlist_item (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_wishlist_id INTEGER NOT NULL REFERENCES vendor_wishlist (id) ON DELETE CASCADE,
    target_type VARCHAR(64) NOT NULL,
    target_id VARCHAR(128) NOT NULL,
    quantity INTEGER NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_wishlist_item_target UNIQUE (vendor_wishlist_id, target_type, target_id)
);

CREATE TABLE vendor_code_storage (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    code VARCHAR(64) NOT NULL,
    phone VARCHAR(64) DEFAULT NULL,
    purpose VARCHAR(32) NOT NULL,
    is_login INTEGER NOT NULL DEFAULT FALSE,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_code_storage_code UNIQUE (code)
);

CREATE TABLE vendor_remember_me_token (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    series VARCHAR(128) NOT NULL,
    token_value VARCHAR(255) NOT NULL,
    last_used_at DATETIME NOT NULL,
    provider_class VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    CONSTRAINT uniq_vendor_remember_me_series UNIQUE (series)
);

CREATE TABLE vendor_customer_order (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    external_order_id VARCHAR(128) NOT NULL,
    order_number VARCHAR(64) DEFAULT NULL,
    status VARCHAR(32) NOT NULL,
    currency VARCHAR(8) NOT NULL,
    gross_cents INTEGER NOT NULL,
    net_cents INTEGER NOT NULL,
    meta JSON NOT NULL,
    placed_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_customer_order_vendor_external UNIQUE (vendor_id, external_order_id)
);
CREATE INDEX idx_vendor_customer_order_vendor_status ON vendor_customer_order (vendor_id, status);

CREATE TABLE vendor_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    action_name VARCHAR(128) NOT NULL,
    payload_json TEXT NOT NULL,
    created_at DATETIME NOT NULL
);
CREATE INDEX idx_vendor_log_vendor_created_at ON vendor_log (vendor_id, created_at);
