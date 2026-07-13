-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 13, 2026 at 01:07 PM
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
-- Database: `cbe_hostel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `allocation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `hostel_id` int(11) NOT NULL,
  `hostel_name` varchar(100) NOT NULL,
  `gender_allowed` varchar(20) NOT NULL,
  `hostel_image` varchar(255) DEFAULT 'default_hostel.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`hostel_id`, `hostel_name`, `gender_allowed`, `hostel_image`) VALUES
(4, 'MJEMA HALL', 'Kike', 'uploads/hostel_1783940291.jpg'),
(5, 'MHAGAMA ROOMS', 'Kike', 'uploads/hostel_1783940333.webp'),
(6, 'MAMA SAMIA HOSTEL', 'Kike', 'uploads/hostel_1783940370.jpg'),
(7, 'TULIA AKSON MALL', 'Kike', 'uploads/hostel_1783940407.jpg'),
(8, 'BENJAMIN MKAPA ROOM', 'Kiume', 'uploads/hostel_1783940455.jpg'),
(9, 'JAKAYA KIKWETE', 'Kiume', 'uploads/hostel_1783940480.jpg'),
(10, 'MAGUFULI HOSTEL', 'Kiume', 'uploads/hostel_1783940504.jpg'),
(11, 'JOB NDUGAI', 'Kiume', 'uploads/hostel_1783940529.jpg'),
(12, 'MAJALIWA HALL', 'Kiume', 'uploads/hostel_1783940559.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `amount`, `control_number`, `status`, `paid_at`) VALUES
(1, 3, 120000.00, '99402926335', 'Paid', '2026-06-17 14:07:18'),
(2, 4, 450000.00, '994400717701', 'Paid', NULL),
(3, 4, 450000.00, '994400763138', 'Paid', NULL),
(4, 6, 450000.00, '994400524713', 'Paid', NULL),
(5, 7, 450000.00, '994400445396', 'Paid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin/Warden'),
(2, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `available_beds` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `hostel_id`, `room_number`, `capacity`, `available_beds`) VALUES
(5, 4, '60', 4, 4),
(6, 5, '100', 4, 4),
(7, 6, '50', 6, 6),
(8, 7, '30', 4, 4),
(9, 8, '30', 6, 6),
(10, 8, '25', 4, 4),
(11, 9, '45', 4, 4),
(12, 10, '100', 6, 6),
(13, 12, '80', 6, 6);

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` text NOT NULL,
  `reg_number` text NOT NULL,
  `phone_number` text NOT NULL,
  `gender` text NOT NULL,
  `profile_image` varchar(255) DEFAULT 'uploads/default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`profile_id`, `user_id`, `full_name`, `reg_number`, `phone_number`, `gender`, `profile_image`) VALUES
(1, 2, 'Salumu Juma Amani', 'CBE/IT/2024/03.7678.01.01', '0712345678', 'Kiume', 'uploads/default_avatar.png'),
(2, 3, 'isa', '0333333', '0617175544', 'Kiume', 'uploads/default_avatar.png'),
(3, 4, 'karimu', '12345', '123333', 'Kiume', 'uploads/default_avatar.png'),
(4, 5, 'duli', '26544456', '666666', 'Kiume', 'uploads/default_avatar.png'),
(5, 6, 'rama', '03.0222.01.01.2024', '123445', 'Kiume', 'uploads/default_avatar.png'),
(6, 7, 'rore', '02222', '23333', 'Kiume', 'uploads/default_avatar.png'),
(7, 8, 'john', '123455', '064535753', 'Kiume', 'uploads/default_avatar.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role_id`) VALUES
(1, 'farida', '$2y$10$wR6fE3kZ.zC0yB4zP1gJduQnS8xVkW1nQ89vQ86r1U6c2n5O6G2b6', 1),
(2, 'salumu', '$2y$10$tZ2pB/03ZkMThKk9VqM8f.6K.U1wbeGbyYVREp1q8H.0u1uP.72P2', 2),
(3, 'isa', '$2y$10$ktHEYxhk7x80Ue.JAfHgQOmdxOFSOXE.KnJ56AlQF.z2Z5SJZq88C', 2),
(4, 'karimu', '$2y$10$9NBzrLxDLajVTU1aGrZTv.5OcFy4PiiTLlrPypc4aYIYt3YcVIvLa', 2),
(5, 'duli', '$2y$10$vFGRkxqyDSZqV5P8KjnIEeQjMGYvKA0H0WBC2.gAGK2SwIapTaz92', 2),
(6, 'rama', '$2y$10$yDmnSyclDPdfWIcAacxwfuz6lS9.XRkJw0.g8oOHHO3krvXEScLwS', 2),
(7, 'rore', '$2y$10$vIMNqcy4Io9UU5g6hJPn6uudKiy4RFRpsgvx1ZGYzpn4pGhzGr9PC', 2),
(8, 'john', '$2y$10$GQhY7sEv83ZhP3O/mI5Yl.9Y9je6wRH3t/mEBRi3c7s/pD7Ok0loC', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`hostel_id`),
  ADD UNIQUE KEY `hostel_name` (`hostel_name`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `allocation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `hostel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `allocations`
--
ALTER TABLE `allocations`
  ADD CONSTRAINT `allocations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allocations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
