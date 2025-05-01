-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2025 at 03:21 PM
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
-- Database: `sameless_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Absent','Late','On-Leave','On-Time','Over-time') NOT NULL,
  `date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `login_date`, `time_in`, `time_out`, `status`, `date`) VALUES
(60, 26, '0000-00-00', '02:12:56', '02:13:02', 'On-Time', '2024-12-16'),
(61, 37, '0000-00-00', '02:13:42', NULL, 'On-Time', '2024-12-16'),
(62, 32, '0000-00-00', '02:14:13', '02:14:15', 'On-Time', '2024-12-16'),
(63, 28, '0000-00-00', '02:14:36', '06:16:23', 'On-Time', '2024-12-16'),
(64, 27, '0000-00-00', '02:15:27', '02:15:27', 'On-Time', '2024-12-16'),
(65, 39, '0000-00-00', '02:15:43', '02:16:25', 'On-Time', '2024-12-16'),
(66, 26, '0000-00-00', '22:53:00', '22:53:10', 'Over-time', '2024-12-18'),
(67, 26, '0000-00-00', '11:20:08', '12:30:49', 'Late', '2024-12-20'),
(68, 28, '0000-00-00', '12:43:11', '12:43:12', 'On-Time', '2024-12-20'),
(69, 30, '0000-00-00', '13:06:52', NULL, 'On-Time', '2024-12-20');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `leave_date` date NOT NULL,
  `status` enum('pending','approved','denied') DEFAULT 'pending',
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `other` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `user_id`, `leave_date`, `status`, `leave_type`, `start_date`, `end_date`, `other`) VALUES
(9, 26, '0000-00-00', 'denied', 'Vacation Leave', '2024-12-30', '2024-12-31', 'adadada'),
(10, 37, '0000-00-00', 'approved', 'Emergency Leave', '2024-12-17', '2024-12-18', 'asdada'),
(11, 32, '0000-00-00', 'denied', 'Vacation Leave', '2024-12-17', '2024-12-18', 'adadasd'),
(12, 28, '0000-00-00', 'denied', 'Sick Leave', '2024-12-19', '2024-12-20', 'adasd'),
(13, 39, '0000-00-00', 'denied', 'Sick Leave', '2024-12-17', '2024-12-18', '123213'),
(14, 39, '0000-00-00', 'denied', 'Sick Leave', '2024-12-17', '2024-12-18', '123213'),
(16, 32, '0000-00-00', 'denied', 'Vacation Leave', '2024-12-30', '2024-12-31', 'asdasd');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests_archive`
--

CREATE TABLE `leave_requests_archive` (
  `archive_id` int(11) NOT NULL,
  `leave_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `leave_date` datetime NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests_archive`
--

INSERT INTO `leave_requests_archive` (`archive_id`, `leave_id`, `user_id`, `full_name`, `leave_date`, `start_date`, `end_date`, `leave_type`, `status`, `deleted_at`) VALUES
(1, 15, 28, 'Gayle David Faller', '0000-00-00 00:00:00', '2025-01-02', '2025-01-03', 'Vacation Leave', 'denied', '2024-12-20 09:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_requests`
--

CREATE TABLE `schedule_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shift_type` enum('morning','afternoon','night') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_requests`
--

INSERT INTO `schedule_requests` (`request_id`, `user_id`, `shift_type`, `start_time`, `end_time`, `status`, `request_date`) VALUES
(1, 26, 'morning', '01:34:00', '16:34:00', 'rejected', '2024-12-20 04:34:52'),
(2, 26, 'morning', '01:34:00', '16:34:00', 'rejected', '2024-12-20 04:37:10'),
(3, 26, 'afternoon', '04:38:00', '12:37:00', 'approved', '2024-12-20 04:37:29'),
(4, 26, 'afternoon', '04:38:00', '12:37:00', 'approved', '2024-12-20 04:37:36'),
(5, 26, 'afternoon', '04:38:00', '12:37:00', 'approved', '2024-12-20 04:37:52'),
(6, 28, 'morning', '12:44:00', '00:42:00', 'approved', '2024-12-20 04:42:21'),
(7, 30, 'afternoon', '15:00:00', '01:00:00', 'rejected', '2024-12-20 04:56:47'),
(8, 30, 'morning', '08:00:00', '20:00:00', 'approved', '2024-12-20 05:06:09'),
(9, 32, 'afternoon', '12:00:00', '00:00:00', 'rejected', '2024-12-20 06:11:51'),
(10, 32, 'afternoon', '12:00:00', '00:59:00', 'approved', '2024-12-20 06:12:55');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `shift_type` enum('morning','afternoon','night') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`shift_id`, `shift_type`, `start_time`, `end_time`) VALUES
(1, 'morning', '08:00:00', '12:00:00'),
(2, 'morning', '07:00:00', '11:00:00'),
(3, 'afternoon', '12:00:00', '18:00:00'),
(4, 'afternoon', '13:00:00', '19:00:00'),
(5, 'night', '18:00:00', '00:00:00'),
(6, 'night', '20:00:00', '01:00:00'),
(7, 'night', '04:14:00', '18:14:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `shift_id` int(11) DEFAULT NULL,
  `shift_type` enum('morning','afternoon','night') NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `original_shift_type` enum('morning','afternoon','night') DEFAULT NULL,
  `original_start_time` time DEFAULT NULL,
  `original_end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `gender`, `full_name`, `email`, `password`, `contact_no`, `role`, `created_at`, `shift_id`, `shift_type`, `start_time`, `end_time`, `original_shift_type`, `original_start_time`, `original_end_time`) VALUES
(25, 'Male', 'Admin', 'admin@gmail.com', '$2y$10$IWtZlA1b6.vNMB2lyxl3jOH8eIrCFgJPcHGHx92kS56lt3AwNwcPu', '09481552911', 'admin', '2024-12-15 17:51:59', 7, 'night', '04:14:00', '18:14:00', NULL, NULL, NULL),
(26, 'Male', 'Don Dave Igot', 'don@gmail.com', '$2y$10$aZPaTuEhJQ7XcOHVvDWCO.8k3Yvl8lII2sOAMLzd6ANG7X3Epifd.', '09481552901', 'user', '2024-12-15 17:54:11', 1, 'morning', '08:00:00', '12:00:00', NULL, NULL, NULL),
(27, 'Female', 'Marc Nino Epe', 'marc1@gmail.com', '$2y$10$9qvaS4ylTEcOUOppTGdPLeXHzpAkw9fmKbqDe8tgh.nzPEHQm2w46', '09481552915', 'user', '2024-12-15 17:55:15', 3, 'afternoon', '12:00:00', '18:00:00', NULL, NULL, NULL),
(28, 'Male', 'Gayle David Faller', 'gayle@gmail.com', '$2y$10$2EKdjkX8Y1wEM.kdlDynsepxS7YP/0eGygmCVq0F69rbKwdLw31/C', '09481552916', 'user', '2024-12-15 17:58:01', 5, 'night', '18:00:00', '00:00:00', NULL, NULL, NULL),
(29, 'Female', 'Johnna Quevedo', 'johnna@gmail.com', '$2y$10$IW2XER7u1Y58Sx8QnpJYqegiHr96fSVWLplmvErMnLZtaXIyH9nQe', '09481552957', 'user', '2024-12-15 17:59:00', 2, 'morning', '07:00:00', '11:00:00', NULL, NULL, NULL),
(30, 'Female', 'Sherlyn Indong', 'sherlyn@gmail.com', '$2y$10$YW//bGfZl2td07iOn1bUk.zv2JPbwABIENyhKYBCiiNtet2/TAW/u', '09481552922', 'user', '2024-12-15 17:59:56', 4, 'afternoon', '13:00:00', '19:00:00', NULL, NULL, NULL),
(31, 'Female', 'Trixie Bautista', 'trixie@gmail.com', '$2y$10$wYvnh6ZyVeBjuajyWEMwUOkAM2IDUcGCpgi/eXExfiO4oHrkcGx92', '09481552944', 'user', '2024-12-15 18:00:18', 2, 'morning', '07:00:00', '11:00:00', NULL, NULL, NULL),
(32, 'Male', 'Xianeri Tampos', 'xianeri@gmail.com', '$2y$10$CwuJBEkRZvSqkC9cFrGvrOWmLOOQH2pb2zX8uO.v1l5BYJXUx0N8W', '09481552333', 'user', '2024-12-15 18:00:48', 3, 'afternoon', '12:00:00', '00:59:00', 'afternoon', '12:00:00', '00:59:00'),
(33, 'Female', 'Cha Taghoy', 'cha@gmail.com', '$2y$10$bLaHJWsMe7p8tIuQ4o9J2.dQH4mCz905NWm5h.Y9pVgY8qFA221FW', '09481552918', 'user', '2024-12-15 18:01:51', 4, 'afternoon', '13:00:00', '19:00:00', NULL, NULL, NULL),
(34, 'Female', 'Debra Ruela', 'debra@gmail.com', '$2y$10$Bo9I/xKLdQv/EiYpxCpvpuASK2XPk54Yno9UdWf/rN7BCH2FvEjwC', '09481552919', 'user', '2024-12-15 18:02:28', 3, 'afternoon', '12:00:00', '18:00:00', NULL, NULL, NULL),
(35, 'Male', 'Jay Laurence Vacante', 'jay@gmail.com', '$2y$10$X6OkgmROBvjMNwrIM0wI0umsPr0VGTyk0ZNhpzBns/n48SdC8hkIi', '09481552222', 'user', '2024-12-15 18:03:24', 4, 'afternoon', '13:00:00', '19:00:00', NULL, NULL, NULL),
(37, 'Male', 'Jun-Rey Amistoso', 'jun@gmail.com', '$2y$10$l3N3L8szzBaLfbnKICADou6se9gPulgVquG.S81mjuLVcsT62dEHm', '09481252915', 'user', '2024-12-15 18:05:18', 6, 'night', '20:00:00', '01:00:00', NULL, NULL, NULL),
(38, 'Female', 'Valerie Cuevas', 'valerie@gmail.com', '$2y$10$V6TvpnQ5VZZemH7rkWpDE.jIkcye/88YAB2chGubcbpwXRx9oNCoS', '09481554915', 'user', '2024-12-15 18:06:19', 6, 'night', '20:00:00', '01:00:00', NULL, NULL, NULL),
(39, 'Female', 'Rosie Gulfan', 'rosie@gmail.com', '$2y$10$kondwZRUcWEh5YtwSXS9zeS0k9BgO9lDb0ZaqNLZKyYnZ8XygVuVe', '09181552913', 'user', '2024-12-15 18:07:30', 3, 'afternoon', '12:00:00', '18:00:00', NULL, NULL, NULL),
(40, 'Male', 'Race Malubay', 'race@gmail.com', '$2y$10$FenlTAHAHNQVg.qi3snmZ.n7PCYkoGAaA.kPeVay1n/i8bvkh6X1C', '09481552886', 'user', '2024-12-15 18:08:11', 2, 'morning', '07:00:00', '11:00:00', NULL, NULL, NULL),
(41, 'Female', 'Pepania Alma Fe', 'pepania@gmail.com', '$2y$10$FYh2nlnPQvrwOFyD.LD6eOdlD6WTDrshIHMNmIiNdhdvSbc9eE5vK', '09482552916', 'user', '2024-12-15 18:09:29', 2, 'morning', '07:00:00', '11:00:00', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_archive`
--

CREATE TABLE `users_archive` (
  `archive_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `role` enum('user','admin') NOT NULL,
  `email` varchar(255) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_archive`
--

INSERT INTO `users_archive` (`archive_id`, `user_id`, `full_name`, `gender`, `contact_no`, `role`, `email`, `deleted_at`) VALUES
(1, 42, 'Judy Ann Dacula', 'Female', '09481551115', 'user', 'judy@gmail.com', '2024-12-20 01:11:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `leave_requests_archive`
--
ALTER TABLE `leave_requests_archive`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `schedule_requests`
--
ALTER TABLE `schedule_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_shift` (`shift_id`);

--
-- Indexes for table `users_archive`
--
ALTER TABLE `users_archive`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `leave_requests_archive`
--
ALTER TABLE `leave_requests_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `schedule_requests`
--
ALTER TABLE `schedule_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users_archive`
--
ALTER TABLE `users_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `schedule_requests`
--
ALTER TABLE `schedule_requests`
  ADD CONSTRAINT `schedule_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
