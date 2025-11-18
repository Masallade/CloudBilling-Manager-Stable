-- =========================================================================
-- DATABASE MIGRATION: Dynamic Tax Type and Tax Rate Settings
-- =========================================================================
-- This script adds tax_type and tax_rate columns to the geopos_system table
-- Run this SQL in your database (phpMyAdmin or MySQL client)
-- =========================================================================

-- Step 1: Add tax_type column
ALTER TABLE `geopos_system` 
ADD COLUMN `tax_type` VARCHAR(50) DEFAULT 'VAT' 
COMMENT 'Tax type name (e.g., VAT, GST, Sales Tax)' 
AFTER `auto_pricing`;

-- Step 2: Add tax_rate column
ALTER TABLE `geopos_system` 
ADD COLUMN `tax_rate` DECIMAL(5,2) DEFAULT 20.00 
COMMENT 'Tax rate percentage' 
AFTER `tax_type`;

-- Step 3: Set default values for existing records
UPDATE `geopos_system` 
SET `tax_type` = 'VAT', `tax_rate` = 20.00 
WHERE `id` = 1 
AND (`tax_type` IS NULL OR `tax_type` = '' OR `tax_rate` IS NULL);

-- Step 4: Verify the changes
SELECT id, currency, tax_type, tax_rate, auto_post, auto_pricing, show_profit_per 
FROM geopos_system 
WHERE id = 1;

-- =========================================================================
-- NOTES:
-- 1. After running this script, go to Settings → Currency Settings
-- 2. You'll see new "Tax Settings" section at the top
-- 3. Configure your Tax Type (e.g., VAT, GST, Sales Tax)
-- 4. Configure your Tax Rate percentage (e.g., 20 for 20%)
-- 5. All invoices and calculations will use these values dynamically
-- =========================================================================

