-- Sample data so the Reports page charts (Booking History, Bookings by Status,
-- New Tricycle Drivers per Barangay, New Driver Registrations) have something
-- to show for the "Today" / "This Week" / "This Month" ranges.
-- Safe to re-run: uses fixed ids that won't collide with existing rows.

-- New driver accounts (for August driver registrations)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(39, 'Ramon Dela Cruz', 'ramon.delacruz@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002001', 'driver', 'active', '2026-08-01 08:10:00', '2026-08-01 08:10:00'),
(40, 'Feliza Santos', 'feliza.santos@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002002', 'driver', 'active', '2026-08-01 09:25:00', '2026-08-01 09:25:00'),
(41, 'Bayani Cruz', 'bayani.cruz@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002003', 'driver', 'active', '2026-08-02 07:40:00', '2026-08-02 07:40:00'),
(42, 'Corazon Diaz', 'corazon.diaz@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002004', 'driver', 'active', '2026-08-02 10:05:00', '2026-08-02 10:05:00'),
(43, 'Mario Torres', 'mario.torres@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002005', 'driver', 'active', '2026-08-02 11:15:00', '2026-08-02 11:15:00'),
(44, 'Elena Ramos', 'elena.ramos@example.com', '$2y$10$hUfKectr8csHUaL9K2ipAOy4llvviLx3OJQDSuu.qBHfncKJwa47C', '09170002006', 'driver', 'active', '2026-08-02 13:50:00', '2026-08-02 13:50:00');

-- New tricycle driver registrations in August (feeds "by barangay" + "new registrations" charts)
INSERT INTO `driver_info` (`id`, `user_id`, `license_no`, `vehicle_no`, `vehicle_type`, `barangay`, `approval_status`, `is_online`, `created_at`, `updated_at`) VALUES
(26, 39, 'LIC-2001', 'TRC-3001', 'Tricycle', 'Maguilling',   'approved', 1, '2026-08-01 08:10:00', '2026-08-01 08:10:00'),
(27, 40, 'LIC-2002', 'TRC-3002', 'Tricycle', 'Calaoagan',    'approved', 0, '2026-08-01 09:25:00', '2026-08-01 09:25:00'),
(28, 41, 'LIC-2003', 'TRC-3003', 'Tricycle', 'Poblacion I',  'pending',  0, '2026-08-02 07:40:00', '2026-08-02 07:40:00'),
(29, 42, 'LIC-2004', 'TRC-3004', 'Tricycle', 'Baung',        'approved', 1, '2026-08-02 10:05:00', '2026-08-02 10:05:00'),
(30, 43, 'LIC-2005', 'TRC-3005', 'Tricycle', 'Santa Barbara', 'rejected', 0, '2026-08-02 11:15:00', '2026-08-02 11:15:00'),
(31, 44, 'LIC-2006', 'TRC-3006', 'Tricycle', 'Poblacion II', 'pending',  0, '2026-08-02 13:50:00', '2026-08-02 13:50:00');

-- Bookings spread across the last two days (feeds Booking History + Bookings by Status charts)
INSERT INTO `bookings` (`id`, `passenger_id`, `driver_id`, `pickup_address`, `pickup_lat`, `pickup_lng`, `dropoff_address`, `dropoff_lat`, `dropoff_lng`, `status`, `fare`, `created_at`, `updated_at`) VALUES
(31, 15, 21, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'completed', 45.00, '2026-08-01 07:05:00', '2026-08-01 07:05:00'),
(32, 16, 22, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'completed', 55.00, '2026-08-01 08:20:00', '2026-08-01 08:20:00'),
(33, 17, 23, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'completed', 62.00, '2026-08-01 09:45:00', '2026-08-01 09:45:00'),
(34, 18, 24, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'Piat National High School', 17.7920000, 121.4640000, 'cancelled', NULL, '2026-08-01 10:30:00', '2026-08-01 10:30:00'),
(35, 19, 25, 'Piat National High School', 17.7920000, 121.4640000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'completed', 30.00, '2026-08-01 12:00:00', '2026-08-01 12:00:00'),
(36, 20, 26, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'rejected', NULL, '2026-08-01 14:10:00', '2026-08-01 14:10:00'),
(37, 15, 27, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'completed', 58.00, '2026-08-01 16:35:00', '2026-08-01 16:35:00'),
(38, 16, 28, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'completed', 41.00, '2026-08-01 18:50:00', '2026-08-01 18:50:00'),
(39, 17, NULL, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'pending', NULL, '2026-08-02 07:15:00', '2026-08-02 07:15:00'),
(40, 18, 30, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'accepted', NULL, '2026-08-02 08:00:00', '2026-08-02 08:00:00'),
(41, 19, 31, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'Maguilling Crossing', 17.8010000, 121.4550000, 'started', NULL, '2026-08-02 09:10:00', '2026-08-02 09:10:00'),
(42, 20, 32, 'Santo Domingo Chapel', 17.7960000, 121.4610000, 'Santa Barbara Elementary School', 17.7830000, 121.4720000, 'completed', 66.00, '2026-08-02 10:20:00', '2026-08-02 10:20:00'),
(43, 15, 33, 'Piat National High School', 17.7920000, 121.4640000, 'Piat Municipal Hall, Piat, Cagayan', 17.7912000, 121.4698000, 'completed', 50.00, '2026-08-02 11:40:00', '2026-08-02 11:40:00'),
(44, 16, NULL, 'Poblacion I Barangay Hall', 17.7895000, 121.4650000, 'Piat Public Market, Piat, Cagayan', 17.7887000, 121.4673000, 'pending', NULL, '2026-08-02 12:55:00', '2026-08-02 12:55:00'),
(45, 17, 34, 'Maguilling Crossing', 17.8010000, 121.4550000, 'Calaoagan Bridge', 17.7780000, 121.4800000, 'completed', 72.00, '2026-08-02 13:30:00', '2026-08-02 13:30:00');
