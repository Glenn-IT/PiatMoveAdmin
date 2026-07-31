ALTER TABLE `driver_info`
  ADD COLUMN `plate_proof_path` varchar(255) DEFAULT NULL AFTER `barangay`,
  ADD COLUMN `license_proof_path` varchar(255) DEFAULT NULL AFTER `plate_proof_path`,
  ADD COLUMN `photo_path` varchar(255) DEFAULT NULL AFTER `license_proof_path`;
