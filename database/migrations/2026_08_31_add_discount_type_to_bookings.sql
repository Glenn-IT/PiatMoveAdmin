-- Migration: Add discount_type to bookings table
-- PiatMove Municipal Tricycle Fare Discount System (20% Statutory Discount)

ALTER TABLE `bookings` 
ADD COLUMN `discount_type` VARCHAR(20) NOT NULL DEFAULT 'regular' AFTER `fare`;
