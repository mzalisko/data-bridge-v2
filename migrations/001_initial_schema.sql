-- DataBridge CRM — Initial Schema
-- Migration: 001
-- Date: 2026-04-08
-- Run: docker exec -i databridge_mysql mysql -u dbuser -psecret databridge < migrations/001_initial_schema.sql

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ─────────────────────────────────────
-- GROUPS
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_groups (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT NULL,
    color       VARCHAR(7)  NOT NULL DEFAULT '#706f70',
    icon        VARCHAR(32) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- SITES
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS sites (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id    INT UNSIGNED NOT NULL,
    name        VARCHAR(255) NOT NULL,
    url         VARCHAR(512) NOT NULL,
    description TEXT NULL,
    logo        VARCHAR(512) NULL,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sites_group FOREIGN KEY (group_id) REFERENCES site_groups(id),
    INDEX idx_group  (group_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- DATA TABLES
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS site_phones (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED NOT NULL,
    label       VARCHAR(100) NULL,
    country_iso VARCHAR(3)   NOT NULL,
    dial_code   VARCHAR(8)   NOT NULL,
    number      VARCHAR(32)  NOT NULL,
    is_primary  TINYINT(1)  NOT NULL DEFAULT 0,
    sort_order  SMALLINT    NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_phones_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_prices (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL,
    label       VARCHAR(255)    NOT NULL,
    amount      DECIMAL(12,2)   NOT NULL,
    currency    VARCHAR(3)      NOT NULL,
    period      VARCHAR(32)     NULL,
    is_visible  TINYINT(1)     NOT NULL DEFAULT 1,
    sort_order  SMALLINT       NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_prices_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_addresses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL,
    label       VARCHAR(100)    NULL,
    country_iso VARCHAR(3)      NOT NULL,
    city        VARCHAR(255)    NOT NULL,
    street      VARCHAR(255)    NULL,
    building    VARCHAR(50)     NULL,
    postal_code VARCHAR(20)     NULL,
    latitude    DECIMAL(10,7)   NULL,
    longitude   DECIMAL(10,7)   NULL,
    is_primary  TINYINT(1)     NOT NULL DEFAULT 0,
    sort_order  SMALLINT       NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_addresses_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_socials (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL,
    platform    VARCHAR(32)     NOT NULL,
    handle      VARCHAR(255)    NOT NULL,
    url         VARCHAR(512)    NOT NULL,
    sort_order  SMALLINT       NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_socials_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_custom_fields (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL,
    field_key   VARCHAR(128)    NOT NULL,
    field_value TEXT            NOT NULL,
    field_type  VARCHAR(32)     NOT NULL DEFAULT 'text',
    sort_order  SMALLINT       NOT NULL DEFAULT 0,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_custom_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    UNIQUE KEY uq_site_key (site_id, field_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- API KEYS
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS api_keys (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL UNIQUE,
    key_hash    VARCHAR(255)    NOT NULL,
    key_prefix  VARCHAR(12)     NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used   TIMESTAMP NULL,
    revoked_at  TIMESTAMP NULL,
    CONSTRAINT fk_apikeys_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_prefix (key_prefix)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- USERS & PERMISSIONS
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255)    NOT NULL,
    email           VARCHAR(255)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('admin','manager','editor','viewer') NOT NULL DEFAULT 'viewer',
    is_active       TINYINT(1)     NOT NULL DEFAULT 1,
    failed_attempts TINYINT        NOT NULL DEFAULT 0,
    locked_until    TIMESTAMP NULL,
    last_login      TIMESTAMP NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NOT NULL,
    group_id    INT UNSIGNED    NULL,
    permission  VARCHAR(64)     NOT NULL,
    granted     TINYINT(1)     NOT NULL DEFAULT 1,
    CONSTRAINT fk_perm_user  FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_perm_group FOREIGN KEY (group_id) REFERENCES site_groups(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_group_perm (user_id, group_id, permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- LOGS
-- ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS sync_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED    NOT NULL,
    synced_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status      VARCHAR(16)     NOT NULL,
    duration_ms SMALLINT        NULL,
    checksum    VARCHAR(71)     NULL,
    error_msg   VARCHAR(255)    NULL,
    CONSTRAINT fk_synclog_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    INDEX idx_site_time (site_id, synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NULL,
    event       VARCHAR(128)    NOT NULL,
    level       ENUM('info','warning','error') NOT NULL DEFAULT 'info',
    context     JSON            NULL,
    ip_address  VARCHAR(45)     NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event   (event),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS batch_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    data_type       VARCHAR(32)     NOT NULL,
    affected_sites  JSON            NOT NULL,
    change_delta    JSON            NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batchlog_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────
-- DEFAULT ADMIN USER
-- password: admin123 (змін перед production!)
-- ─────────────────────────────────────
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES (
    'Administrator',
    'admin@databridge.local',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

SET foreign_key_checks = 1;
