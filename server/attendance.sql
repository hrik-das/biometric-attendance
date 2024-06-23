-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2024 at 04:49 PM
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
-- Database: `attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_cred`
--

CREATE TABLE `admin_cred` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_cred`
--

INSERT INTO `admin_cred` (`id`, `username`, `password`) VALUES
(1, 'bas', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
  `date` date NOT NULL,
  `present` int(11) NOT NULL,
  `absent` int(11) NOT NULL,
  `dar` float NOT NULL COMMENT 'Daily Attendance Rate',
  `mar` float NOT NULL COMMENT 'Monthly Attendance Rate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_log`
--

CREATE TABLE `attendance_log` (
  `s_no` mediumint(8) UNSIGNED NOT NULL COMMENT 'Doesn''t map to anything',
  `roll_no` smallint(4) UNSIGNED NOT NULL,
  `full_name` varchar(20) NOT NULL,
  `semester` tinyint(3) UNSIGNED NOT NULL,
  `date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='RECORDS ONLY PRESENT STUDENTS';

-- --------------------------------------------------------

--
-- Table structure for table `esp_edu_state`
--

CREATE TABLE `esp_edu_state` (
  `mode` char(1) NOT NULL,
  `fingerprint_id` smallint(4) NOT NULL,
  `roll_no` smallint(4) NOT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `server_block` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays_2024`
--

CREATE TABLE `holidays_2024` (
  `id` int(11) NOT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays_2024`
--

INSERT INTO `holidays_2024` (`id`, `holiday_name`, `holiday_date`) VALUES
(1, 'New Year\'s Day', '2024-01-01'),
(2, 'Guru Gobind Singh Jayanti', '2024-01-05'),
(3, 'Makar Sankranti', '2024-01-14'),
(4, 'Pongal', '2024-01-15'),
(5, 'Republic Day', '2024-01-26'),
(6, 'Basant Panchami', '2024-02-05'),
(7, 'Guru Ravidas Jayanti', '2024-02-12'),
(8, 'Mahashivratri', '2024-02-20'),
(9, 'Holi', '2024-03-10'),
(10, 'Good Friday', '2024-04-05'),
(11, 'Mahavir Jayanti', '2024-04-15'),
(12, 'Eid al-Fitr', '2024-04-26'),
(13, 'Buddha Purnima', '2024-05-05'),
(14, 'Independence Day', '2024-08-15'),
(15, 'Janmashtami', '2024-08-30'),
(16, 'Gandhi Jayanti', '2024-10-02'),
(17, 'Dussehra', '2024-10-15'),
(18, 'Eid al-Adha', '2024-10-03'),
(19, 'Diwali', '2024-10-24'),
(20, 'Guru Nanak Jayanti', '2024-10-23'),
(21, 'Guru Tegh Bahadur Martyrdom Day', '2024-11-24'),
(22, 'Christmas Day', '2024-12-25');

-- --------------------------------------------------------

--
-- Table structure for table `users_all`
--

CREATE TABLE `users_all` (
  `fingerprint_id` smallint(4) UNSIGNED NOT NULL,
  `roll_no` smallint(4) UNSIGNED NOT NULL,
  `full_name` varchar(30) NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `contact` char(13) DEFAULT NULL,
  `semester` tinyint(1) UNSIGNED NOT NULL,
  `enlist_date` date NOT NULL DEFAULT curdate(),
  `delist_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
  ADD PRIMARY KEY (`date`);

--
-- Indexes for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD PRIMARY KEY (`s_no`);

--
-- Indexes for table `holidays_2024`
--
ALTER TABLE `holidays_2024`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_all`
--
ALTER TABLE `users_all`
  ADD PRIMARY KEY (`fingerprint_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_log`
--
ALTER TABLE `attendance_log`
  MODIFY `s_no` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Doesn''t map to anything';

--
-- AUTO_INCREMENT for table `holidays_2024`
--
ALTER TABLE `holidays_2024`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users_all`
--
ALTER TABLE `users_all`
  MODIFY `fingerprint_id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
