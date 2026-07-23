-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 03:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `piatmove`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `security_question`, `security_answer`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@piatmove.com', '$2y$10$ME3nbM0uecD5SBQybEVTouSNgtA9WsaipoXyaIjxwvFksoDkN4BLa', 'What is your mother\'s maiden name?', '$2y$10$pKfYRZhLOVn9M4hKkMTiseZ3Y8fYr1t.OqM7RUMHDTBer..K/g8wy', '2026-06-26 11:32:37', '2026-06-28 12:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `passenger_id` int(10) UNSIGNED NOT NULL,
  `driver_id` int(10) UNSIGNED DEFAULT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `pickup_lat` decimal(10,7) NOT NULL,
  `pickup_lng` decimal(10,7) NOT NULL,
  `dropoff_address` varchar(255) NOT NULL,
  `dropoff_lat` decimal(10,7) NOT NULL,
  `dropoff_lng` decimal(10,7) NOT NULL,
  `status` enum('pending','accepted','rejected','started','completed','cancelled') NOT NULL DEFAULT 'pending',
  `fare` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `passenger_id`, `driver_id`, `pickup_address`, `pickup_lat`, `pickup_lng`, `dropoff_address`, `dropoff_lat`, `dropoff_lng`, `status`, `fare`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'SM City Pampanga', 15.0794000, 120.6200000, 'Robinsons Starmills', 15.0841000, 120.6317000, 'pending', NULL, '2026-06-26 11:57:32', '2026-06-26 11:57:32'),
(2, 6, 8, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'completed', NULL, '2026-06-26 16:01:05', '2026-06-26 17:06:36');

-- --------------------------------------------------------

--
-- Table structure for table `driver_info`
--

CREATE TABLE `driver_info` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `license_no` varchar(50) NOT NULL,
  `vehicle_no` varchar(50) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `current_lat` decimal(10,7) DEFAULT NULL,
  `current_lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver_info`
--

INSERT INTO `driver_info` (`id`, `user_id`, `license_no`, `vehicle_no`, `vehicle_type`, `barangay`, `approval_status`, `is_online`, `current_lat`, `current_lng`, `created_at`, `updated_at`) VALUES
(1, 3, 'LIC-001', 'ABC-1234', 'Sedan', 'Poblacion I', 'approved', 0, NULL, NULL, '2026-06-26 11:41:30', '2026-06-26 12:23:32'),
(2, 5, 'LIC-002', 'ABC-2234', 'Sedan', 'Poblacion II', 'approved', 0, NULL, NULL, '2026-06-26 12:00:17', '2026-06-26 12:03:21'),
(3, 8, 'GAB7253', 'BAHS782', 'Tricycle', 'Santa Barbara', 'approved', 1, NULL, NULL, '2026-06-26 17:05:04', '2026-06-26 17:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `fcm_tokens`
--

CREATE TABLE `fcm_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` enum('passenger','driver') NOT NULL DEFAULT 'passenger',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Juan dela Cruz', 'juan@example.com', '$2y$10$SeuXwRt/bI7cXI67DmdxXevDetmrYOd8YoMRozsaarBfKV5/K3sCW', '09171234567', 'passenger', 'active', '2026-06-26 11:37:26', '2026-06-26 11:37:26'),
(3, 'Pedro Driver', 'pedro@example.com', '$2y$10$7VtQgLC/LxU1oeX4UGHuWuztUVnWQHpP9gzwCtVI4QsrBcifY5Tm6', '09181234567', 'driver', 'active', '2026-06-26 11:41:30', '2026-06-26 11:41:30'),
(4, 'Juan Penduko', 'juan2@example.com', '$2y$10$iZiL7G6vGXsfIAx4mjBdpuIumIOBCwYS2W/tol5ikkIY2NJrHKcKi', '09132234567', 'passenger', 'active', '2026-06-26 11:45:11', '2026-06-26 11:45:11'),
(5, 'Pedros Manong', 'pedro2@example.com', '$2y$10$sJ/JSO016TXX9g.Whii4RezpRIcH0l.BAWBF95VdEjoDRU3l/s2Fy', '09181234327', 'driver', 'active', '2026-06-26 12:00:17', '2026-06-26 12:00:17'),
(6, 'Pagurayan Glenard', 'glenard0823@gmail.com', '$2y$10$3ksSe7u0qKImo9ho0nWwkOF/uxnK3TQU0yIfIkuRa0oHz6iz4.E6C', '639395064641', 'passenger', 'active', '2026-06-26 15:45:39', '2026-06-26 15:45:39'),
(7, 'Pagurayan Glenard', 'glenard082@gmail.com', '$2y$10$p6HbwxsSdn8p0Nu1f.hsBOvQjrahLXoTtluON4xzOP2FYPtFVlfAi', '639395064641', 'passenger', 'active', '2026-06-26 15:48:02', '2026-06-26 15:48:02'),
(8, 'Lucky Baltazar', 'lucky@gmail.com', '$2y$10$md8wf.gKLXOq8J4jQGlfi.V1yooJ/cdxPK/bbuJ.2l4ORd4EA5RFO', '+44639395064641', 'driver', 'active', '2026-06-26 17:05:04', '2026-06-26 17:05:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_passenger` (`passenger_id`),
  ADD KEY `idx_bookings_driver` (`driver_id`),
  ADD KEY `idx_bookings_status` (`status`);

--
-- Indexes for table `driver_info`
--
ALTER TABLE `driver_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `driver_info`
--
ALTER TABLE `driver_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_booking_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_info`
--
ALTER TABLE `driver_info`
  ADD CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  ADD CONSTRAINT `fk_fcm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
