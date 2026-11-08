-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2026 at 09:20 AM
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
-- Database: `login2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(130) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','registrar','staff') NOT NULL DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(2, '', 'kel@gmail.com', '$2y$10$v7y5Wqy9NX.rIueOrJhiGeimh01VF1fIyElHSTiGiHqM/n6yKblkG', 'staff', '2026-11-01 12:10:55'),
(3, 'System Developer', 'superadmin@school.edu', '$2y$10$e0NRz7w7Yf1mQy7q9C1o1uJ1s0w2o3X4r5T6y7U8i9O0p1A2s3D4f', 'super_admin', '2026-11-06 02:23:31'),
(4, 'System Admin', 'admin@school.edu', '$2y$10$jFLvxDSBMzfgcpB8BhfwfOsAqo6OwIo5cn2tEdnuPnsgLAc3MZOhS', 'super_admin', '2026-11-06 02:36:12'),
(5, 'Walt', 'test@gmail.com', '$2y$10$HhAycd/2fFwbhShpZx6aQOPvmzJ9KK/4uGqscJNQkXrxvpgKMp.8y', 'staff', '2026-11-08 07:14:15');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nationality` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`profile_id`, `user_id`, `student_id`, `middle_name`, `phone_no`, `address`, `program`, `year_level`, `updated_at`, `nationality`, `religion`, `marital_status`) VALUES
(1, 1, '2026-0001', 'P', '03123261', NULL, 'BSIT', '3rd Year', '2026-11-01 11:41:23', NULL, NULL, NULL),
(2, 2, '202402114', 'Paaaa', '06216166', 'OnTheMoon', 'BSCS', '3rd Year', '2026-11-08 07:30:30', 'Filipino', 'Catholic', 'Single'),
(12, 4, '202611425', NULL, '095217746', NULL, 'BSHM', '3rd Year', '2026-11-03 12:50:06', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(130) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `birthday` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `firstName`, `lastName`, `email`, `password`, `gender`, `birthday`, `profile_pic`) VALUES
(1, 'caa', 'wya', 'las@gmail.com', '$2y$10$cZvrir8lN03oLM1mN0kxwe89BD53v97vqLw4l9fuoUEIqFG8zbbIW', NULL, NULL, NULL),
(2, 'Mew', 'Tho', 'wan@gmail.com', '$2y$10$.Wl9Su7SlMdAErO0EDwNmeKw3/3NWw56qdP0sgzNp/oxbTpWJx1we', 'Male', '01/30/2006', 'uploads/profile_2_1794123752.jpg'),
(4, 'test', 'tast', 'ta@gmail.com', '$2y$10$9LNQXho6JmsVk4x0NSMVOOUW5Xxih38TefY9/snX7rCi.9ipsBDue', NULL, NULL, NULL);

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
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`Id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
