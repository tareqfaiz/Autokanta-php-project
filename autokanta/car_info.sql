-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2024 at 10:55 PM
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
-- Database: `car_info`
--

-- --------------------------------------------------------

--
-- Table structure for table `car`
--

CREATE TABLE `car` (
  `licnumber` char(7) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `model` int(11) DEFAULT NULL,
  `owner` char(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car`
--

INSERT INTO `car` (`licnumber`, `color`, `model`, `owner`, `user_id`) VALUES
('CES-528', 'blue', 2010, '281182-070W', 3),
('FTO-232', 'Green', 2025, '201298-2887', 1),
('HUT-444', 'gold', 2006, '120760-093B', 5),
('MSR-235', 'Black', 2025, '121092-9878', 9),
('ROA-630', 'gray', 2011, '080173-169T', 4);

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `licnumber` char(7) DEFAULT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `fine_reason` varchar(255) NOT NULL,
  `fine_date` date NOT NULL,
  `paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine`
--

INSERT INTO `fine` (`id`, `user_id`, `licnumber`, `fine_amount`, `fine_reason`, `fine_date`, `paid`) VALUES
(1, 1, 'FTO-232', 50.00, 'wrong parking', '2024-11-13', 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:04:49'),
(2, 1, 'Your update request was rejected. Reason: we do not find this car', '2024-11-13 10:07:25'),
(3, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:19:00'),
(4, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:19:30'),
(5, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:28:57'),
(6, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:31:36'),
(7, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:31:49'),
(8, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 10:31:57'),
(9, 1, 'Your update request has been approved.', '2024-11-13 11:05:46'),
(10, 1, 'Your update request has been approved.', '2024-11-13 11:08:04'),
(11, 1, 'Your update request has been approved.', '2024-11-13 11:38:42'),
(12, 1, 'Your update request has been approved.', '2024-11-13 11:38:53'),
(13, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 11:39:14'),
(14, 1, 'Your update request has been approved.', '2024-11-13 11:39:25'),
(15, 1, 'Your update request has been approved.', '2024-11-13 11:39:30'),
(16, 1, 'Your update request has been approved.', '2024-11-13 11:39:32'),
(17, 1, 'Your update request has been approved.', '2024-11-13 11:39:33'),
(18, 1, 'Your update request has been approved.', '2024-11-13 11:39:36'),
(19, 1, 'Your update request was rejected. Reason: we do not find this car', '2024-11-13 11:39:47'),
(20, 1, 'Your update request has been approved.', '2024-11-13 11:49:16'),
(21, 1, 'Your update request has been approved.', '2024-11-13 11:49:50'),
(22, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:50:26'),
(23, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:50:57'),
(24, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:51:02'),
(25, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:51:24'),
(26, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:52:10'),
(27, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:52:27'),
(28, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:52:54'),
(29, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 11:56:00'),
(30, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 12:05:28'),
(31, 1, 'Your update request has been approved.', '2024-11-13 12:11:00'),
(32, 1, 'Your update request was rejected. Reason: multiple request', '2024-11-13 12:11:12'),
(33, 1, 'Your update request has been approved.', '2024-11-13 12:11:28'),
(34, 1, 'Your update request has been approved.', '2024-11-13 12:11:34'),
(35, 1, 'Your update request has been approved.', '2024-11-13 12:11:38'),
(36, 1, 'Your update request has been approved.', '2024-11-13 12:11:40'),
(37, 1, 'Your update request was rejected. Reason: you changed rrepeatedly', '2024-11-13 12:16:46'),
(38, 1, 'Your update request was rejected. Reason: you changed rrepeatedly', '2024-11-13 12:17:09'),
(39, 1, 'Your update request has been approved.', '2024-11-13 12:17:18'),
(40, 1, 'Your update request has been approved.', '2024-11-13 13:07:15'),
(41, 1, 'Your update request was rejected. Reason: your name can not match with ID', '2024-11-13 13:08:03'),
(42, 1, 'Your update request has been approved.', '2024-11-13 13:16:32'),
(43, 1, 'Your update request has been approved.', '2024-11-13 13:21:36'),
(44, 9, 'Your update request was rejected. Reason: you changed repeatedly', '2024-11-13 13:25:31'),
(45, 9, 'Your update request was rejected. Reason: you changed repeatedly', '2024-11-13 13:25:38'),
(46, 1, 'Your update request has been approved.', '2024-11-13 13:25:56'),
(47, 1, 'Your update request has been approved.', '2024-11-13 13:26:08'),
(48, 1, 'Your update request has been approved.', '2024-11-13 13:26:16'),
(49, 9, 'Your update request has been approved.', '2024-11-13 13:28:46'),
(50, 9, 'Your update request has been approved.', '2024-11-13 13:29:45'),
(51, 9, 'Your update request has been approved.', '2024-11-13 13:30:04'),
(52, 1, 'Your update request has been approved.', '2024-11-13 13:39:51'),
(53, 1, 'Your update request has been approved.', '2024-11-13 13:40:16'),
(54, 1, 'Your update request has been approved.', '2024-11-13 13:43:48'),
(55, 1, 'Your update request has been approved.', '2024-11-13 13:45:34'),
(56, 1, 'Your update request has been approved.', '2024-11-13 13:46:18'),
(57, 1, 'Your update request has been approved.', '2024-11-13 13:46:42'),
(58, 1, 'Your update request has been approved.', '2024-11-13 13:58:02'),
(59, 1, 'Your update request was rejected. Reason: repating', '2024-11-13 13:59:46'),
(60, 1, 'Your update request was rejected. Reason: repeating', '2024-11-13 14:08:07'),
(61, 1, 'Your update request has been approved.', '2024-11-13 14:08:11'),
(62, 1, 'Your update request has been approved.', '2024-11-13 15:14:54'),
(63, 1, 'Your update request was rejected. Reason: repeating', '2024-11-13 15:16:09'),
(64, 1, 'Your update request has been approved.', '2024-11-13 16:11:41'),
(65, 1, 'Your update request has been approved.', '2024-11-13 16:11:53'),
(66, 1, 'Your update request has been approved.', '2024-11-13 16:28:18'),
(67, 1, 'Your update request was rejected. Reason: repeating', '2024-11-13 16:30:10'),
(68, 1, 'Your update request has been approved.', '2024-11-13 16:32:17'),
(69, 1, 'Your update request was rejected. Reason: name did not match with ID', '2024-11-13 16:33:02'),
(70, 2, 'Your update request has been approved.', '2024-11-13 16:37:04'),
(71, 1, 'Your update request was rejected. Reason: repeating', '2024-11-13 16:39:10'),
(72, 1, 'Your update request was rejected. Reason: repeating', '2024-11-13 16:40:31'),
(73, 1, 'Your update request was rejected. Reason: name did not match with ID', '2024-11-13 16:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `socnumber` char(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person`
--

INSERT INTO `person` (`socnumber`, `name`, `address`, `phone`, `user_id`) VALUES
('080173-169T', 'Matti Miettinen', 'Koivukuja 25', '040-1842950', 4),
('120760-093B', 'Tapio Tamminen', 'Tammistontie 18', '0400-576397', 5),
('121092-9878', 'Jani Koivonen', 'Vantanportti 45c', '0400550089', 9),
('200292-195H', 'Teemu Tamminen', 'Tammistontie 18', '040-9740768', 6),
('201298-2887', 'Alex Venalainen', 'London 23C', '0400550089', 1),
('281182-070W', 'Anne Autoilija', 'Kanervapolku 2', '050-1640837', 3);

-- --------------------------------------------------------

--
-- Table structure for table `update_requests`
--

CREATE TABLE `update_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `new_first_name` varchar(100) DEFAULT NULL,
  `new_last_name` varchar(100) DEFAULT NULL,
  `new_address` varchar(255) DEFAULT NULL,
  `new_phone` varchar(20) DEFAULT NULL,
  `new_socnumber` varchar(20) DEFAULT NULL,
  `new_licnumber` varchar(20) DEFAULT NULL,
  `new_car_model` varchar(100) DEFAULT NULL,
  `new_car_color` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comment` text DEFAULT NULL,
  `status` enum('approved','rejected') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `update_requests`
--

INSERT INTO `update_requests` (`id`, `user_id`, `new_first_name`, `new_last_name`, `new_address`, `new_phone`, `new_socnumber`, `new_licnumber`, `new_car_model`, `new_car_color`, `created_at`, `comment`, `status`) VALUES
(27, 1, 'Alex', 'Melani', 'Peltolantie 20C', '0400550089', NULL, 'CRP-294', '2025', 'Yellow', '2024-11-13 16:24:00', 'name did not match with ID', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`) VALUES
(1, 'alexsuomi', '$2y$10$9Kj38aqLSSvZQ0RiXo27MeMNk5IAkEF6tCyjiajwZWi165.LQSPOi', 'alexsuomi@gmail.com', 'user'),
(2, 'admin', '$2y$10$9hFV0rYM6FGlXQfH66JQzOL5Ntf5FmONYEDLWb/sF.fVHwewSoc/m', 'admin@finnbangla.fi', 'admin'),
(9, 'jani', '$2y$10$FyMk04gLz16T2e5wJvYyNuXEXlZbBVrZBiIdQvxDpSNUrQi34IIuu', 'jani@gmail.com', 'user'),
(10, 'maria', '$2y$10$WTj7a2gUCwdwNWKdzi1oGODFyyhv/U04PbqHmM7keeGsBJCFsMdSq', 'maria@gmail.com', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `car`
--
ALTER TABLE `car`
  ADD PRIMARY KEY (`licnumber`),
  ADD KEY `fk_owner` (`owner`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `licnumber` (`licnumber`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`socnumber`),
  ADD UNIQUE KEY `uc_socnumber_user_id` (`socnumber`,`user_id`);

--
-- Indexes for table `update_requests`
--
ALTER TABLE `update_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fine`
--
ALTER TABLE `fine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `update_requests`
--
ALTER TABLE `update_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `car`
--
ALTER TABLE `car`
  ADD CONSTRAINT `car_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `person` (`socnumber`),
  ADD CONSTRAINT `fk_owner` FOREIGN KEY (`owner`) REFERENCES `person` (`socnumber`) ON DELETE CASCADE;

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fine_ibfk_2` FOREIGN KEY (`licnumber`) REFERENCES `car` (`licnumber`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `update_requests`
--
ALTER TABLE `update_requests`
  ADD CONSTRAINT `update_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
