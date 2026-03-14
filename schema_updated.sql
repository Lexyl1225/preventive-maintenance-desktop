-- schema.sql
-- Database: pm_db1
-- Generated to match existing structure (as of February 2026)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------
-- Table structure for users
-- ----------------------------------------------------------------------
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user',
  `user_uid` varchar(128) DEFAULT NULL,
  `password_hash` text,
  `salt` text,
  `created_at` datetime DEFAULT NULL,
  `last_signed_in` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------------------------------------------------
-- Table structure for load_balancing_records
-- ----------------------------------------------------------------------
CREATE TABLE `load_balancing_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `branch` varchar(100) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `panel` varchar(255) DEFAULT NULL,
  `mcb` int DEFAULT NULL,
  `mainV` decimal(8,2) DEFAULT NULL,
  `wires` varchar(255) DEFAULT NULL,
  `conductedBy` varchar(255) DEFAULT NULL,
  `witnessBy` varchar(255) DEFAULT NULL,
  `certifiedBy` varchar(255) DEFAULT NULL,
  `witnessTenant` varchar(255) DEFAULT NULL,
  `circType` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `refId` varchar(100) DEFAULT NULL,
  `remarks` text,
  `vll` decimal(6,2) DEFAULT NULL,
  `vln` decimal(6,2) DEFAULT NULL,
  `vph` decimal(6,2) DEFAULT NULL,
  `nameplate` decimal(12,2) DEFAULT NULL,
  `meas_ia` decimal(8,2) DEFAULT NULL,
  `meas_ib` decimal(6,2) DEFAULT NULL,
  `meas_ic` decimal(6,2) DEFAULT NULL,
  `meas_van` decimal(6,2) DEFAULT NULL,
  `meas_vbn` decimal(6,2) DEFAULT NULL,
  `meas_vcn` decimal(6,2) DEFAULT NULL,
  `pf_a` decimal(4,2) DEFAULT NULL,
  `pf_b` decimal(4,2) DEFAULT NULL,
  `pf_c` decimal(4,2) DEFAULT NULL,
  `phaseA` decimal(10,2) DEFAULT NULL,
  `phaseB` decimal(10,2) DEFAULT NULL,
  `phaseC` decimal(10,2) DEFAULT NULL,
  `totalLoad` decimal(12,2) DEFAULT NULL,
  `loads` json DEFAULT NULL,
  `fileName` varchar(255) DEFAULT NULL,
  `savedAt` datetime DEFAULT NULL,
  `kvaTotal` decimal(12,3) DEFAULT NULL,
  `vl1l2Ind` decimal(6,2) DEFAULT NULL,
  `vl1l3Ind` decimal(6,2) DEFAULT NULL,
  `vl2l3Ind` decimal(6,2) DEFAULT NULL,
  `vl1nInd` decimal(6,2) DEFAULT NULL,
  `vl2nInd` decimal(6,2) DEFAULT NULL,
  `vl3nInd` decimal(6,2) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------------------------------------------------
-- Table structure for maintenance_records
-- ----------------------------------------------------------------------
CREATE TABLE `maintenance_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `BranchCode` varchar(100) DEFAULT NULL,
  `BranchName` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `equipment` varchar(255) DEFAULT NULL,
  `task` text,
  `status` varchar(50) DEFAULT NULL,
  `performedBy` varchar(255) DEFAULT NULL,
  `verifiedBy` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `nextDue` date DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `BranchCode` (`BranchCode`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------------------------------------------------
-- Table structure for megger_tests
-- ----------------------------------------------------------------------
CREATE TABLE `megger_tests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `savedAt` datetime DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------------------------------------------------
-- Table structure for pm_checklist
-- ----------------------------------------------------------------------
CREATE TABLE `pm_checklist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `branchName` varchar(255) DEFAULT NULL,
  `branchCode` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,           -- note: stored as varchar(50), not date
  `conductedBy` varchar(255) DEFAULT NULL,
  `refNumber` varchar(100) DEFAULT NULL,
  `sigConducted` varchar(255) DEFAULT NULL,
  `sigWitnessed` varchar(255) DEFAULT NULL,
  `sigStoreManager` varchar(255) DEFAULT NULL,
  `sigStoreRep` varchar(255) DEFAULT NULL,
  `overallRecommendations` text,
  `categories` json DEFAULT NULL,
  `savedAt` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------
-- Table structure for maintenance_uploads
-- ----------------------------------------------------------------------
CREATE TABLE `maintenance_uploads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `record_id` int DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(512) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `uploader` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `record_id` (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;