-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 02:38 AM
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
-- Database: `saga_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `database_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `logo_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `code`, `database_name`, `address`, `phone`, `status`, `created_at`, `logo_url`) VALUES
(2, 'SAGA TOKO', 'BKT0001', 'saga_tenant_bkt0001', 'Jln. Raya Padang Lua', '082388329542', 'active', '2025-12-20 05:10:50', '/uploads/logos/tenant_2_1766553739868.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` enum('super_admin','tenant_owner','manager','cashier') NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `role`, `branch_id`, `tenant_id`, `is_active`, `created_at`) VALUES
(1, 'admin@sagatoko.com', '$2a$10$WUxFbxeTtch/ROWnh7TQne5oRUk1FujQpZr.lQ52jbFllQ5HV0OtO', 'Super Admin', 'super_admin', NULL, NULL, 1, '2025-12-17 09:59:37'),
(8, 'admintika@saga.com', '$2a$10$bFVGwsSWEJ3ofZluS/Rgw.HkcnpjsyXPLIiNILnKM/2xnZDlRbuzW', 'Atika', 'cashier', 1, 2, 1, '2025-12-20 05:25:12'),
(9, 'adminhaikal@saga.com', '$2a$10$D09NYAnuJL12f8D022wlT.iJMS/EbJ8VlgaltZqOoA2S5YcKbxGrW', 'Haikal', 'tenant_owner', NULL, 2, 1, '2025-12-20 05:26:32'),
(10, 'rjbradmin@saga.com', '$2a$10$3N4r7P7w3aIB26ZBw4nYAuABQ/pVDeOhCmgUk2KFfkubrNThfRxai', 'Rizky', 'manager', 2, 2, 1, '2025-12-20 06:00:35'),
(11, 'rjbrgudang@saga.com', '$2a$10$etN5O2E3cq1KeXKCK0/Jd.4QgKsDQFomdeFMFrvYUGW5YbOKPkYfa', 'Haikal', 'manager', 1, 2, 1, '2025-12-20 06:01:32'),
(12, 'rjbrbalingka@saga.com', '$2a$10$QldqUvDq5Y38XR0HjZ0Krev0h5FvEIxl5PW2KKBx76RKYB0DCHIDO', 'Admin Balingka', 'manager', 4, 2, 1, '2025-12-22 10:26:32'),
(20, 'adminrifky@saga.com', '$2a$10$DUq/7lZRWRe30WV58pgIVuAE0ZGs/VEK4D3jIoHD1nki8OXGM0LFq', 'Admin Rifky', 'manager', 5, 2, 1, '2025-12-25 03:51:04'),
(21, 'kasirpb@saga.com', '$2a$10$uMcpX/VFR7bJtpGM4ZV5qOt2wA10vT6JrmSZw.0KucoM2VYv52Z1K', 'kasirpb', 'cashier', 5, 2, 1, '2025-12-25 03:51:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
