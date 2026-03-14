-- Migration: add kvaTotal and individual voltage columns to load_balancing_records
-- Run this once on the live database.
-- MySQL 8.0 compatible (no IF NOT EXISTS on ADD COLUMN).

ALTER TABLE `load_balancing_records`
  ADD COLUMN `kvaTotal`  decimal(12,3) DEFAULT NULL,
  ADD COLUMN `vl1l2Ind`  decimal(6,2)  DEFAULT NULL,
  ADD COLUMN `vl1l3Ind`  decimal(6,2)  DEFAULT NULL,
  ADD COLUMN `vl2l3Ind`  decimal(6,2)  DEFAULT NULL,
  ADD COLUMN `vl1nInd`   decimal(6,2)  DEFAULT NULL,
  ADD COLUMN `vl2nInd`   decimal(6,2)  DEFAULT NULL,
  ADD COLUMN `vl3nInd`   decimal(6,2)  DEFAULT NULL;
