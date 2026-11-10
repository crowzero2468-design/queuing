-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2026 at 03:28 PM
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
-- Database: `ci_queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `queue_tickets`
--

CREATE TABLE `queue_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(255) NOT NULL,
  `status` enum('waiting','serving','skipped','served') DEFAULT 'waiting',
  `room_id` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `queue_tickets`
--

INSERT INTO `queue_tickets` (`id`, `ticket_number`, `status`, `room_id`, `type`, `created_at`, `updated_at`) VALUES
(1, 'CHEMO-001', 'served', NULL, 'chemo', '2026-02-25 05:57:35', '2026-02-25 13:57:35'),
(2, 'CHEMO-002', 'served', NULL, 'chemo', '2026-02-25 05:57:36', '2026-02-25 13:57:36'),
(3, 'CHEMO-003', 'served', NULL, 'chemo', '2026-02-25 05:58:43', '2026-02-25 13:58:43'),
(4, 'CHEMO-004', 'served', NULL, 'chemo', '2026-02-25 05:58:43', '2026-02-25 13:58:43'),
(5, 'CHEMO-005', 'served', NULL, 'chemo', '2026-02-25 06:03:30', '2026-02-25 14:03:30'),
(6, 'CHEMO-006', 'served', NULL, 'chemo', '2026-02-25 06:03:30', '2026-02-25 14:03:30'),
(7, 'CHEMO-007', 'served', NULL, 'chemo', '2026-02-25 06:03:30', '2026-02-25 14:03:30'),
(8, 'CHEMO-008', 'served', NULL, 'chemo', '2026-02-25 06:03:30', '2026-02-25 14:03:30'),
(9, 'CHEMO-009', 'served', NULL, 'chemo', '2026-02-25 06:03:36', '2026-02-25 14:03:36'),
(10, 'CHEMO-010', 'served', NULL, 'chemo', '2026-02-25 06:03:37', '2026-02-25 14:03:37'),
(11, 'CHEMO-011', 'served', NULL, 'chemo', '2026-02-25 06:03:39', '2026-02-25 14:03:39'),
(12, 'CHEMO-012', 'served', NULL, 'chemo', '2026-02-25 06:04:49', '2026-02-25 14:04:49'),
(13, 'CHEMO-013', 'served', NULL, 'chemo', '2026-02-25 06:04:49', '2026-02-25 14:04:49'),
(14, 'CHEMO-014', 'skipped', NULL, 'chemo', '2026-02-25 06:04:49', '2026-02-25 14:04:49'),
(15, 'CHEMO-015', 'served', NULL, 'chemo', '2026-02-25 06:17:05', '2026-02-25 14:17:05'),
(16, 'CHEMO-016', 'served', NULL, 'chemo', '2026-02-25 06:17:20', '2026-02-25 14:17:20'),
(17, 'CHEMO-017', 'served', NULL, 'chemo', '2026-02-25 06:17:21', '2026-02-25 14:17:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `queue_tickets`
--
ALTER TABLE `queue_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
