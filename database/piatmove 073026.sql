-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2026 at 01:36 PM
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
(1, 'Admin', 'admin@piatmove.com', '$2y$10$VG1BbMFaO4PepuaxrkhfOeSDWD0LCehbKgp1A2Lv44serGki3V8km', 'What is your mother\'s maiden name?', '$2y$10$pKfYRZhLOVn9M4hKkMTiseZ3Y8fYr1t.OqM7RUMHDTBer..K/g8wy', '2026-06-26 11:32:37', '2026-07-24 12:55:47');

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
(3, 19, 31, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'started', NULL, '2026-07-11 01:16:00', '2026-07-11 01:16:00'),
(4, 18, 34, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'completed', 68.00, '2026-07-13 07:58:00', '2026-07-13 07:58:00'),
(5, 20, 22, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'accepted', NULL, '2026-06-28 05:46:00', '2026-06-28 05:46:00'),
(6, 15, 28, 'Piat National High School', 17.7920000, 121.4640000, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'completed', 61.00, '2026-07-10 03:09:00', '2026-07-10 03:09:00'),
(7, 15, 24, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'completed', 48.00, '2026-06-30 10:59:00', '2026-06-30 10:59:00'),
(8, 15, 14, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'completed', 64.00, '2026-06-26 09:23:00', '2026-06-26 09:23:00'),
(9, 15, 31, 'Piat National High School', 17.7920000, 121.4640000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 30.00, '2026-07-14 04:19:00', '2026-07-14 04:19:00'),
(10, 16, 24, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'completed', 38.00, '2026-07-03 06:30:00', '2026-07-03 06:30:00'),
(11, 16, 30, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'completed', 53.00, '2026-07-14 06:13:00', '2026-07-14 06:13:00'),
(12, 16, 25, 'Piat National High School', 17.7920000, 121.4640000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'completed', 68.00, '2026-06-29 09:56:00', '2026-06-29 09:56:00'),
(13, 20, 26, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'cancelled', NULL, '2026-07-10 05:47:00', '2026-07-10 05:47:00'),
(14, 20, 22, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'rejected', NULL, '2026-07-04 03:44:00', '2026-07-04 03:44:00'),
(15, 15, 35, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'completed', 36.00, '2026-07-02 11:07:00', '2026-07-02 11:07:00'),
(16, 20, 28, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Piat National High School', 17.7920000, 121.4640000, 'accepted', NULL, '2026-07-03 09:20:00', '2026-07-03 09:20:00'),
(17, 18, 22, 'Piat National High School', 17.7920000, 121.4640000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'accepted', NULL, '2026-07-06 12:40:00', '2026-07-06 12:40:00'),
(18, 17, 26, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'completed', 63.00, '2026-07-16 10:56:00', '2026-07-16 10:56:00'),
(19, 17, 25, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'Piat National High School', 17.7920000, 121.4640000, 'completed', 77.00, '2026-07-07 05:00:00', '2026-07-07 05:00:00'),
(20, 15, 21, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 39.00, '2026-06-25 05:22:00', '2026-06-25 05:22:00'),
(21, 18, 22, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 41.00, '2026-07-10 06:43:00', '2026-07-10 06:43:00'),
(22, 15, 14, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Piat National High School', 17.7920000, 121.4640000, 'cancelled', NULL, '2026-06-24 04:42:00', '2026-06-24 04:42:00'),
(23, 17, 34, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 60.00, '2026-06-26 03:02:00', '2026-06-26 03:02:00'),
(24, 20, 26, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'rejected', NULL, '2026-07-12 22:16:00', '2026-07-12 22:16:00'),
(25, 20, 33, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'cancelled', NULL, '2026-07-09 06:10:00', '2026-07-09 06:10:00'),
(26, 15, 30, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 31.00, '2026-07-09 12:59:00', '2026-07-09 12:59:00'),
(27, 17, 28, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'completed', 44.00, '2026-07-14 02:54:00', '2026-07-14 02:54:00'),
(28, 20, 22, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'started', NULL, '2026-07-13 13:50:00', '2026-07-13 13:50:00'),
(29, 18, 34, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'completed', 74.00, '2026-07-19 11:20:00', '2026-07-19 11:20:00'),
(30, 15, 28, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Piat National High School', 17.7920000, 121.4640000, 'completed', 68.00, '2026-07-08 11:57:00', '2026-07-08 11:57:00');

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
  `barangay` varchar(50) NOT NULL DEFAULT '',
  `plate_proof_path` varchar(255) DEFAULT NULL,
  `license_proof_path` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
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

INSERT INTO `driver_info` (`id`, `user_id`, `license_no`, `vehicle_no`, `vehicle_type`, `barangay`, `plate_proof_path`, `license_proof_path`, `photo_path`, `approval_status`, `is_online`, `current_lat`, `current_lng`, `created_at`, `updated_at`) VALUES
(6, 13, 'qwe-123', '123-qwe', 'Tricycle', 'Maguilling', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:46:36', '2026-07-23 09:46:36'),
(7, 14, 'qwe-123', '123-qwe', 'Tricycle', 'Calaoagan', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:47:01', '2026-07-23 09:47:01'),
(8, 21, 'LIC-1100', 'TRC-2100', 'Tricycle', 'Poblacion I', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(9, 22, 'LIC-1101', 'TRC-2101', 'Tricycle', 'Poblacion I', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(10, 23, 'LIC-1102', 'TRC-2102', 'Tricycle', 'Poblacion I', NULL, NULL, NULL, 'pending', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(11, 24, 'LIC-1103', 'TRC-2103', 'Tricycle', 'Poblacion II', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(12, 25, 'LIC-1104', 'TRC-2104', 'Tricycle', 'Poblacion II', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(13, 26, 'LIC-1105', 'TRC-2105', 'Tricycle', 'Santa Barbara', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(14, 27, 'LIC-1106', 'TRC-2106', 'Tricycle', 'Santa Barbara', NULL, NULL, NULL, 'rejected', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(15, 28, 'LIC-1107', 'TRC-2107', 'Tricycle', 'Santo Domingo', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(16, 29, 'LIC-1108', 'TRC-2108', 'Tricycle', 'Santo Domingo', NULL, NULL, NULL, 'pending', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(17, 30, 'LIC-1109', 'TRC-2109', 'Tricycle', 'Maguilling', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(18, 31, 'LIC-1110', 'TRC-2110', 'Tricycle', 'Calaoagan', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(19, 32, 'LIC-1111', 'TRC-2111', 'Tricycle', 'Aquib', NULL, NULL, NULL, 'pending', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(20, 33, 'LIC-1112', 'TRC-2112', 'Tricycle', 'Baung', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(21, 34, 'LIC-1113', 'TRC-2113', 'Tricycle', 'Minanga', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(22, 35, 'LIC-1114', 'TRC-2114', 'Tricycle', 'Villa Rey (San Gaspar)', NULL, NULL, NULL, 'approved', 0, NULL, NULL, '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(25, 38, 'bshy-23423', 'bisdj-qwe-123124123', 'Tricycle', 'Dugayung', 'uploads/drivers/d4adfe12c5d883fe.png', 'uploads/drivers/fc856c33fccca586.png', 'uploads/drivers/3c60bbc131910230.png', 'approved', 0, NULL, NULL, '2026-07-31 10:56:58', '2026-07-31 10:56:58');

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
(13, 'Juan Santos', 'juan@gmail.com', '$2y$10$EMZrBXOOejdpnQygGENr9uvbPz39IIsnsJWICg6fGDQcX39AHaFYm', '09123123123', 'driver', 'active', '2026-07-23 09:46:36', '2026-07-23 09:46:36'),
(14, 'Juan Pedro', 'Juanped@gmail.com', '$2y$10$ikQ/Lz9Kd2VFgWpdUCzdG.xeKUnKGoCtK4k6fyUQWSLI19FfDouue', '09123213123', 'driver', 'active', '2026-07-23 09:47:01', '2026-07-23 09:47:01'),
(15, 'Maria Cruz', 'maria.cruz@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234501', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(16, 'Jose Ramirez', 'jose.ramirez@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234502', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(17, 'Ana Bautista', 'ana.bautista@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234503', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(18, 'Pedro Manalo', 'pedro.manalo@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234504', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(19, 'Liza Domingo', 'liza.domingo@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234505', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(20, 'Carlo Reyes', 'carlo.reyes@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09171234506', 'passenger', 'active', '2026-07-23 09:58:04', '2026-07-23 09:58:04'),
(21, 'Ramon Villanueva', 'ramon.villanueva@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001100', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(22, 'Teresa Aquino', 'teresa.aquino@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001101', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(23, 'Danilo Santos', 'danilo.santos@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001102', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(24, 'Marites Garcia', 'marites.garcia@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001103', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(25, 'Ernesto Lopez', 'ernesto.lopez@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001104', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(26, 'Corazon Rivera', 'corazon.rivera@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001105', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(27, 'Bienvenido Torres', 'bienvenido.torres@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001106', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(28, 'Josefina Pascual', 'josefina.pascual@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001107', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(29, 'Rogelio Fernandez', 'rogelio.fernandez@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001108', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(30, 'Wilfredo Mendoza', 'wilfredo.mendoza@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001109', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(31, 'Editha Castro', 'editha.castro@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001110', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(32, 'Alberto Gonzales', 'alberto.gonzales@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001111', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(33, 'Remedios Flores', 'remedios.flores@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001112', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(34, 'Nestor Ocampo', 'nestor.ocampo@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001113', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(35, 'Leonora Salazar', 'leonora.salazar@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170001114', 'driver', 'active', '2026-07-23 09:58:05', '2026-07-23 09:58:05'),
(38, 'San Isidro', 'sanisid@gmail.com', '$2y$10$BROiEUmVegGkvdO0Cb/zEO7mvGjozTCsfkiqMKa.fl3UuoTBRQIYO', '09579741956', 'driver', 'active', '2026-07-31 10:56:58', '2026-07-31 10:56:58');

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `driver_info`
--
ALTER TABLE `driver_info`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `fcm_tokens`
--
ALTER TABLE `fcm_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

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
