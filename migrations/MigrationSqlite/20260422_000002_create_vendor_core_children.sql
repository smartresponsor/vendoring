CREATE TABLE vendor_profile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    display_name VARCHAR(255) DEFAULT NULL,
    about TEXT DEFAULT NULL,
    website VARCHAR(512) DEFAULT NULL,
    socials JSON DEFAULT NULL,
    seo_title VARCHAR(255) DEFAULT NULL,
    seo_description TEXT DEFAULT NULL,
    public_profile_status VARCHAR(32) NOT NULL DEFAULT 'draft',
    public_profile_published_at DATETIME DEFAULT NULL,
    CONSTRAINT uniq_vendor_profile_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    logo_path VARCHAR(1024) DEFAULT NULL,
    banner_path VARCHAR(1024) DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    CONSTRAINT uniq_vendor_media_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_billing (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    iban VARCHAR(64) DEFAULT NULL,
    swift VARCHAR(64) DEFAULT NULL,
    payout_method VARCHAR(32) NOT NULL DEFAULT 'bank',
    billing_email VARCHAR(255) DEFAULT NULL,
    payout_status VARCHAR(32) NOT NULL DEFAULT 'idle',
    CONSTRAINT uniq_vendor_billing_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_security (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    CONSTRAINT uniq_vendor_security_vendor UNIQUE (vendor_id),
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE TABLE vendor_document (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    type VARCHAR(64) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    expires_at DATETIME DEFAULT NULL,
    uploader_id INTEGER DEFAULT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE INDEX idx_vendor_document_vendor_created
    ON vendor_document (vendor_id, created_at DESC);

CREATE TABLE vendor_attachment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    category VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE
);

CREATE INDEX idx_vendor_attachment_vendor_created
    ON vendor_attachment (vendor_id, created_at DESC);
