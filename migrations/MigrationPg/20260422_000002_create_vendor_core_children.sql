CREATE TABLE vendor_profile (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    display_name VARCHAR(255) DEFAULT NULL,
    about TEXT DEFAULT NULL,
    website VARCHAR(512) DEFAULT NULL,
    socials JSON DEFAULT NULL,
    seo_title VARCHAR(255) DEFAULT NULL,
    seo_description TEXT DEFAULT NULL,
    public_profile_status VARCHAR(32) NOT NULL DEFAULT 'draft',
    public_profile_published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    CONSTRAINT uniq_vendor_profile_vendor UNIQUE (vendor_id)
);

CREATE TABLE vendor_media (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    logo_path VARCHAR(1024) DEFAULT NULL,
    banner_path VARCHAR(1024) DEFAULT NULL,
    gallery JSON DEFAULT NULL,
    CONSTRAINT uniq_vendor_media_vendor UNIQUE (vendor_id)
);

CREATE TABLE vendor_billing (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    iban VARCHAR(64) DEFAULT NULL,
    swift VARCHAR(64) DEFAULT NULL,
    payout_method VARCHAR(32) NOT NULL DEFAULT 'bank',
    billing_email VARCHAR(255) DEFAULT NULL,
    payout_status VARCHAR(32) NOT NULL DEFAULT 'idle',
    CONSTRAINT uniq_vendor_billing_vendor UNIQUE (vendor_id)
);

CREATE TABLE vendor_security (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    CONSTRAINT uniq_vendor_security_vendor UNIQUE (vendor_id)
);

CREATE TABLE vendor_document (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    type VARCHAR(64) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    uploader_id INTEGER DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX idx_vendor_document_vendor_created
    ON vendor_document (vendor_id, created_at DESC);

CREATE TABLE vendor_attachment (
    id SERIAL PRIMARY KEY,
    vendor_id INTEGER NOT NULL REFERENCES vendor (id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    category VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL
);

CREATE INDEX idx_vendor_attachment_vendor_created
    ON vendor_attachment (vendor_id, created_at DESC);
