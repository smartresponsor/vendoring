CREATE TABLE vendor_passport (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    tax_id VARCHAR(64) NOT NULL,
    country VARCHAR(8) NOT NULL,
    verified INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_passport_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_analytics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    metrics JSON NOT NULL,
    CONSTRAINT uniq_vendor_analytics_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_ledger_binding (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    ledger_vendor_id VARCHAR(64) NOT NULL,
    CONSTRAINT uniq_vendor_ledger_binding_vendor UNIQUE (vendor_id),
    CONSTRAINT uniq_vendor_ledger_binding_external UNIQUE (ledger_vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_user_assignment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    role VARCHAR(64) NOT NULL,
    status VARCHAR(32) NOT NULL,
    is_primary INTEGER NOT NULL DEFAULT 0,
    granted_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    CONSTRAINT uniq_vendor_user_assignment_vendor_user UNIQUE (vendor_id, user_id)
);

CREATE INDEX idx_vendor_user_assignment_vendor_status
    ON vendor_user_assignment (vendor_id, status);

CREATE INDEX idx_vendor_user_assignment_user_status
    ON vendor_user_assignment (user_id, status);

CREATE TABLE vendor_address (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    country_code VARCHAR(2) DEFAULT NULL,
    region VARCHAR(128) DEFAULT NULL,
    locality VARCHAR(128) DEFAULT NULL,
    postal_code VARCHAR(32) DEFAULT NULL,
    address_line_1 VARCHAR(255) DEFAULT NULL,
    address_line_2 VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_address_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_iban (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    iban VARCHAR(64) NOT NULL,
    swift VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_iban_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_profile_avatar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_profile_avatar_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_profile_cover (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_profile_cover_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_document_attachment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_document_id INTEGER NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT uniq_vendor_document_attachment_document UNIQUE (vendor_document_id),
    FOREIGN KEY (vendor_document_id) REFERENCES vendor_document (id) ON DELETE CASCADE
);

CREATE TABLE vendor_media_attachment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_media_id INTEGER NOT NULL,
    kind VARCHAR(32) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    position INTEGER DEFAULT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (vendor_media_id) REFERENCES vendor_media (id) ON DELETE CASCADE
);

CREATE INDEX idx_vendor_media_attachment_media_kind
    ON vendor_media_attachment (vendor_media_id, kind);
