-- SQL schema for Preventive Maintenance application
-- Run this once to create the necessary tables in your MySQL database.

CREATE DATABASE IF NOT EXISTS pm_db1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pm_db1;

-- maintenance records (formerly epm_records_v1)
-- This table backs the main maintenance pages. Data is collected via
-- various forms (e.g. maintenance-related pages) and displayed
-- on list/detail screens (notably not load balancing, megger, or
-- checklists).
CREATE TABLE IF NOT EXISTS maintenance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    BranchCode VARCHAR(100) DEFAULT NULL,
    BranchName VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    equipment VARCHAR(255) DEFAULT NULL,
    task TEXT,
    status VARCHAR(50) DEFAULT NULL,
    performedBy VARCHAR(255) DEFAULT NULL,
    verifiedBy VARCHAR(255) DEFAULT NULL,
    date DATE DEFAULT NULL,
    nextDue DATE DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_date (date),
    KEY idx_branchcode (BranchCode)
);

-- load balancing records (formerly epm_load_balancing_v1)
-- Used by load_balancing_form.php to save each entry and
-- loaded in load_balancing_records.php for viewing/searching entries.
-- Numeric fields are now typed appropriately so decimal values
-- entered via the form are stored without truncation.
CREATE TABLE IF NOT EXISTS load_balancing_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch VARCHAR(100) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    panel VARCHAR(255) DEFAULT NULL,
    mcb INT DEFAULT NULL,
    mainV DECIMAL(8,2) DEFAULT NULL,
    wires VARCHAR(255) DEFAULT NULL,
    conductedBy VARCHAR(255) DEFAULT NULL,
    witnessBy VARCHAR(255) DEFAULT NULL,
    certifiedBy VARCHAR(255) DEFAULT NULL,
    witnessTenant VARCHAR(255) DEFAULT NULL,
    circType VARCHAR(50) DEFAULT NULL,
    date DATE DEFAULT NULL,
    time TIME DEFAULT NULL,
    refId VARCHAR(100) DEFAULT NULL,
    remarks TEXT,
    vll DECIMAL(6,2) DEFAULT NULL,
    vln DECIMAL(6,2) DEFAULT NULL,
    vph DECIMAL(6,2) DEFAULT NULL,
    -- increased size to handle large nameplate ratings
    nameplate DECIMAL(12,2) DEFAULT NULL,
    meas_ia DECIMAL(8,2) DEFAULT NULL,
    meas_ib DECIMAL(8,2) DEFAULT NULL,
    meas_ic DECIMAL(8,2) DEFAULT NULL,
    meas_van DECIMAL(8,2) DEFAULT NULL,
    meas_vbn DECIMAL(8,2) DEFAULT NULL,
    meas_vcn DECIMAL(8,2) DEFAULT NULL,
    pf_a DECIMAL(4,2) DEFAULT NULL,
    pf_b DECIMAL(4,2) DEFAULT NULL,
    pf_c DECIMAL(4,2) DEFAULT NULL,
    phaseA DECIMAL(10,2) DEFAULT NULL,
    phaseB DECIMAL(10,2) DEFAULT NULL,
    phaseC DECIMAL(10,2) DEFAULT NULL,
    totalLoad DECIMAL(12,2) DEFAULT NULL,
    loads JSON DEFAULT NULL,
    fileName VARCHAR(255) DEFAULT NULL,
    savedAt DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- megger test files (formerly megger_test_files_v1)
-- Entries originate from megger_test_form.php and are viewed in
-- megger_test_viewer.php (which reads from this table). Each row
-- holds metadata plus the JSON payload for a test.  Numeric readings
-- live inside the JSON blob and will be preserved as numbers or
-- strings depending on browser behavior.
CREATE TABLE IF NOT EXISTS megger_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) DEFAULT NULL,
    savedAt DATETIME DEFAULT NULL,
    data JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- pm checklist (formerly epm_pm_checklist_v1)
-- Populated by pm_checklist.php forms and displayed in
-- pm_checklist_records.php for review/printing.
CREATE TABLE IF NOT EXISTS pm_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branchName VARCHAR(255) DEFAULT NULL,
    branchCode VARCHAR(100) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    date VARCHAR(50) DEFAULT NULL,
    conductedBy VARCHAR(255) DEFAULT NULL,
    refNumber VARCHAR(100) DEFAULT NULL,
    sigConducted VARCHAR(255) DEFAULT NULL,
    sigWitnessed VARCHAR(255) DEFAULT NULL,
    sigStoreManager VARCHAR(255) DEFAULT NULL,
    sigStoreRep VARCHAR(255) DEFAULT NULL,
    overallRecommendations TEXT,
    categories JSON DEFAULT NULL,
    savedAt DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- users table for authentication or reference
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) UNIQUE,
    full_name VARCHAR(255) DEFAULT NULL,
    role VARCHAR(50) DEFAULT 'user',
    user_uid VARCHAR(128) DEFAULT NULL,
    password_hash TEXT DEFAULT NULL,
    salt TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT NULL,
    last_signed_in DATETIME DEFAULT NULL
);

-- store list snapshots (saved from store_list.php, viewed in store_list_records.php)
CREATE TABLE IF NOT EXISTS store_list_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fileName VARCHAR(255) DEFAULT NULL,
    snapshot JSON DEFAULT NULL,
    totalMain INT DEFAULT 0,
    totalSat INT DEFAULT 0,
    totalRedemption INT DEFAULT 0,
    totalAreas INT DEFAULT 0,
    savedAt DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- maintenance uploads (images/videos attached to maintenance records)
CREATE TABLE IF NOT EXISTS maintenance_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT DEFAULT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(512) DEFAULT NULL,
    file_type VARCHAR(100) DEFAULT NULL,
    file_size BIGINT DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    uploader VARCHAR(255) DEFAULT NULL,
    deleted TINYINT(1) DEFAULT 0,
    deleted_at DATETIME DEFAULT NULL,
    deleted_by VARCHAR(255) DEFAULT NULL,
    KEY idx_record (record_id)
);
