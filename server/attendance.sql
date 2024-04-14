SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `admin_cred` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin_cred` (`id`, `username`, `password`) VALUES
(1, 'bas', '12345');

CREATE TABLE `attendance_log` (
  `s_no` mediumint(8) UNSIGNED NOT NULL,
  `roll_no` smallint(4) UNSIGNED NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='RECORDS ONLY PRESENT STUDENTS';

INSERT INTO `attendance_log` (`s_no`, `roll_no`, `date`) VALUES
(1, 5150, '2024-04-14');

CREATE TABLE `users_all` (
  `fingerprint_id` smallint(4) UNSIGNED NOT NULL,
  `roll_no` smallint(4) UNSIGNED NOT NULL,
  `full_name` varchar(30) NOT NULL,
  `semester` tinyint(1) UNSIGNED NOT NULL,
  `enroll_date` date NOT NULL DEFAULT current_timestamp(),
  `delist_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Information about all users';

INSERT INTO `users_all` (`fingerprint_id`, `roll_no`, `full_name`, `semester`, `enroll_date`, `delist_date`) VALUES
(1, 5150, 'Plaban', 6, '2024-04-14', NULL);

ALTER TABLE `admin_cred`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `attendance_log`
  ADD PRIMARY KEY (`s_no`);

ALTER TABLE `users_all`
  ADD PRIMARY KEY (`fingerprint_id`);

ALTER TABLE `admin_cred`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `attendance_log`
  MODIFY `s_no` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `users_all`
  MODIFY `fingerprint_id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
