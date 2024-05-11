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
-- Table structure for table `attendance_log`
--

CREATE TABLE `attendance_log` (
  `s_no` mediumint(8) UNSIGNED NOT NULL COMMENT 'Doesn''t map to anything',
  `roll_no` smallint(4) UNSIGNED NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Information about all users';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_log`
--
ALTER TABLE `attendance_log`
  ADD PRIMARY KEY (`s_no`);

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
-- AUTO_INCREMENT for table `users_all`
--
ALTER TABLE `users_all`
  MODIFY `fingerprint_id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
