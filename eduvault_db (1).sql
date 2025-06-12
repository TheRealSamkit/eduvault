-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 12, 2025 at 03:03 PM
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
-- Database: `eduvault_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `activity_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`activity_id`, `id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, NULL, 1, 'File verify ID: 1', NULL, '::1', '2025-06-11 06:38:35'),
(2, NULL, 1, 'File verify ID: 4', NULL, '::1', '2025-06-11 06:38:45'),
(3, NULL, 1, 'File verify ID: 5', NULL, '::1', '2025-06-11 06:39:10'),
(5, 4, 1, 'block', NULL, '::1', '2025-06-11 08:02:27'),
(6, 4, 1, 'unblock', NULL, '::1', '2025-06-11 08:02:55'),
(7, NULL, 1, 'delete', NULL, '::1', '2025-06-11 08:03:13'),
(8, NULL, 1, 'Report dismissed ID: 5', NULL, '::1', '2025-06-11 08:39:32'),
(9, NULL, 1, 'Report resolved ID: 11', NULL, '::1', '2025-06-11 08:54:41'),
(10, NULL, 1, 'Report dismissed ID: 14', NULL, '::1', '2025-06-11 09:49:42'),
(11, NULL, 1, 'Report dismissed ID: 13', NULL, '::1', '2025-06-11 09:49:45'),
(12, NULL, 1, 'Report dismissed ID: 12', NULL, '::1', '2025-06-11 09:49:47'),
(13, NULL, 1, 'Report dismissed ID: 18', NULL, '::1', '2025-06-11 10:01:07'),
(14, NULL, 1, 'Report dismissed ID: 17', NULL, '::1', '2025-06-11 10:01:10'),
(15, NULL, 1, 'Report dismissed ID: 16', NULL, '::1', '2025-06-11 10:01:13'),
(16, NULL, 1, 'Report dismissed ID: 15', NULL, '::1', '2025-06-11 10:01:16');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','moderator') NOT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$Q9oTT87zOSUl1wat6LkUSuf/F2ehpzSBl4NOfAMtkp1uEqKYxZGmi', 'super_admin', '2025-06-12 11:46:51', '2025-06-02 13:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE `boards` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `name`) VALUES
(1, 'CBSE'),
(4, 'GBSE'),
(2, 'ICSE'),
(6, 'Other'),
(3, 'RBSE'),
(5, 'State Board');

-- --------------------------------------------------------

--
-- Table structure for table `book_listings`
--

CREATE TABLE `book_listings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` enum('Available','Donated') DEFAULT 'Available',
  `image_path` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `board_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_listings`
--

INSERT INTO `book_listings` (`id`, `user_id`, `title`, `status`, `image_path`, `location`, `created_at`, `board_id`, `subject_id`) VALUES
(1, 3, 'Mathematics Class 10', 'Available', 'http://localhost/eduvault/uploads/images/684711b80674e.jpg', 'Ahmedabad', '2025-06-09 16:54:16', 4, 1),
(2, 3, 'Chemistry Class 12', 'Available', 'http://localhost/eduvault/uploads/images/684711e40f36f.jpg', 'Ahmedabad', '2025-06-09 16:55:00', 4, 3),
(3, 3, ' Web Technologies Text Book', 'Available', 'http://localhost/eduvault/uploads/images/6847121f15ca2.jpg', 'Ahmedabad', '2025-06-09 16:55:59', 4, 13),
(5, 4, 'Test Size limit', 'Available', 'http://localhost/eduvault/uploads/images/6847baf3a92f2.jpg', 'Ahmedabad', '2025-06-10 04:50:45', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `digital_files`
--

CREATE TABLE `digital_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year` varchar(10) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 1,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `digital_files`
--

INSERT INTO `digital_files` (`id`, `user_id`, `title`, `description`, `file_path`, `subject`, `course`, `year`, `verified`, `file_type`, `file_size`, `upload_date`) VALUES
(2, 2, 'Physics Practical', 'Physics practical file for Class 12.', 'uploads/files/physics_practical.pdf', 'Physics', 'Class 12', '2024', 1, 'pdf', '', '2025-06-09 08:17:54'),
(3, 3, 'Web Technologies Guide', 'Guide to modern web technologies.', 'uploads/files/web_tech.pdf', 'Web Technologies', 'BCA', '2023', 0, 'pdf', '', '2025-06-09 08:17:54'),
(4, 3, 'Test Material', 'Just A test for uploading study materail', '../uploads/files/6846a087ca4e5.docx', 'Web Tech', 'Diploma', '2012', 1, 'docx', '', '2025-06-09 08:51:19'),
(5, 3, 'Test Material', 'Just A test for uploading study materail', '../uploads/files/6846a12ec69d7.docx', 'Web Tech', 'Diploma', '2012', 1, 'docx', '', '2025-06-09 08:54:06'),
(6, 4, 'Test 1 JPG', 'Test ', '../uploads/files/6847c245d5e59.jpg', 'Test', 'B.Tech', '2025', 1, 'jpg', '', '2025-06-10 05:27:33'),
(7, 4, 'Test File Size UPload', 'Test 1', '../uploads/files/68491ab1def27.pdf', 'Web Tech', 'Diploma', '2025', 0, 'pdf', '0.666753', '2025-06-11 05:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `downloads`
--

CREATE TABLE `downloads` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `downloads`
--

INSERT INTO `downloads` (`id`, `file_id`, `user_id`, `downloaded_at`) VALUES
(2, 2, 3, '2025-06-09 08:17:54'),
(4, 2, 3, '2025-06-09 08:34:04'),
(5, 5, 3, '2025-06-09 16:05:20'),
(6, 6, 4, '2025-06-10 05:28:24');

-- --------------------------------------------------------

--
-- Table structure for table `file_feedback`
--

CREATE TABLE `file_feedback` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_feedback`
--

INSERT INTO `file_feedback` (`id`, `file_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(2, 2, 3, 4, 'Good practical guide.', '2025-06-09 08:17:54'),
(17, 6, 4, 5, 'ok', '2025-06-10 05:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `mimes`
--

CREATE TABLE `mimes` (
  `id` int(11) NOT NULL,
  `extension` varchar(255) DEFAULT NULL,
  `mime_types` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mimes`
--

INSERT INTO `mimes` (`id`, `extension`, `mime_types`) VALUES
(1, 'pdf', 'application/pdf'),
(2, 'doc', 'application/msword'),
(3, 'docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
(4, 'ppt', 'application/vnd.ms-powerpoint'),
(5, 'pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
(6, 'txt', 'text/plain'),
(7, 'jpg', 'image/jpeg'),
(8, 'jpeg', 'image/jpeg'),
(9, 'png', 'image/png'),
(10, 'png', 'image/png');

-- --------------------------------------------------------

--
-- Table structure for table `reported_content`
--

CREATE TABLE `reported_content` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `content_type` enum('book','file','user') NOT NULL,
  `content_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','resolved','dismissed') DEFAULT 'pending',
  `admin_id` int(11) DEFAULT 1,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reported_content`
--

INSERT INTO `reported_content` (`id`, `reporter_id`, `content_type`, `content_id`, `reason`, `status`, `admin_id`, `resolution_notes`, `created_at`, `resolved_at`) VALUES
(1, 4, 'file', 6, 'Bad 1', 'pending', 1, NULL, '2025-06-10 06:08:39', NULL),
(2, 4, 'user', 3, 'Bye User', 'pending', 1, NULL, '2025-06-10 08:11:48', NULL),
(3, 4, 'user', 3, 'Noob', 'pending', 1, NULL, '2025-06-10 08:20:11', NULL),
(4, 4, 'user', 3, 'Test 2', 'pending', 1, NULL, '2025-06-10 08:20:55', NULL),
(5, 3, 'file', 7, 'test', 'dismissed', 1, 'ok', '2025-06-11 08:37:50', '2025-06-11 08:39:32'),
(6, 3, 'book', 7, 'Reported', 'pending', 1, NULL, '2025-06-11 08:48:43', NULL),
(7, 3, 'book', 7, 'Reported', 'pending', 1, NULL, '2025-06-11 08:49:14', NULL),
(8, 3, 'book', 7, 'Reported', 'pending', 1, NULL, '2025-06-11 08:49:16', NULL),
(9, 3, 'book', 7, 'Reported', 'pending', 1, NULL, '2025-06-11 08:49:32', NULL),
(10, 3, 'book', 7, 'Reported', 'pending', 1, NULL, '2025-06-11 08:49:55', NULL),
(11, 3, 'file', 6, 'Test OKKKK', 'resolved', 1, '', '2025-06-11 08:54:26', '2025-06-11 08:54:41'),
(12, 3, 'file', 7, 'Repoer test vjfbvjfb', 'dismissed', 1, '', '2025-06-11 09:48:12', '2025-06-11 09:49:47'),
(13, 3, 'file', 7, 'Repoer test vjfbvjfb', 'dismissed', 1, '', '2025-06-11 09:48:43', '2025-06-11 09:49:45'),
(14, 3, 'file', 7, 'Repoer test vjfbvjfb', 'dismissed', 1, '', '2025-06-11 09:49:17', '2025-06-11 09:49:42'),
(15, 3, 'user', 4, 'Test user', 'dismissed', 1, '', '2025-06-11 09:59:40', '2025-06-11 10:01:16'),
(16, 3, 'user', 4, 'Test USer', 'dismissed', 1, '', '2025-06-11 10:00:22', '2025-06-11 10:01:13'),
(17, 3, 'user', 4, 'Another Test\\r\\n', 'dismissed', 1, '', '2025-06-11 10:00:47', '2025-06-11 10:01:10'),
(18, 3, 'user', 4, 'Another test', 'dismissed', 1, '', '2025-06-11 10:01:01', '2025-06-11 10:01:07'),
(19, 3, 'file', 7, 'Test ', 'pending', 1, NULL, '2025-06-11 10:13:06', NULL),
(20, 3, 'book', 5, 'Report Book', 'pending', 1, NULL, '2025-06-11 10:18:53', NULL),
(21, 3, 'book', 5, 'Report Book Test', 'pending', 1, NULL, '2025-06-11 12:47:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(4, 'Biology'),
(3, 'Chemistry'),
(5, 'Computer Science'),
(9, 'Economics'),
(7, 'History'),
(1, 'Mathematics'),
(11, 'Philosophy'),
(2, 'Physics'),
(8, 'Political Science'),
(10, 'Psychology'),
(12, 'Sociology'),
(6, 'Software Engineering'),
(13, 'Web Technologies');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'EduVault', 'Website name', NULL, '2025-06-02 13:30:16'),
(2, 'site_description', 'Educational Resource Sharing Platform', 'Website description', NULL, '2025-06-02 13:30:16'),
(3, 'max_file_size', '10485760', 'Maximum file size in bytes (10MB)', NULL, '2025-06-02 13:30:16'),
(4, 'allowed_file_types', 'pdf,doc,docx,ppt,pptx,jpg,png,jpeg,txt', 'Allowed file extensions', NULL, '2025-06-10 09:04:02'),
(5, 'items_per_page', '12', 'Number of items to show per page', NULL, '2025-06-02 13:30:16'),
(7, 'max_image_size', '2097125', 'maximum image size in bytes (2MB)', NULL, '2025-06-10 09:02:21'),
(8, 'allowed_image_types', 'jpg,png,jpeg', 'Allowed image extensions', NULL, '2025-06-10 08:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar_path` varchar(255) NOT NULL DEFAULT 'default.png',
  `password` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `status` enum('blocked','active') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` timestamp NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar_path`, `password`, `location`, `phone`, `status`, `created_at`, `last_active`, `latitude`, `longitude`) VALUES
(2, 'Jane Smith', 'jane@example.com', 'default.png', '$2y$10$MX/jQNLabvw1LEqzDQZMb.CcsGiQpI0izxJbxHbHdwpwHoT0omzrO', 'Mumbai', '9123456789', 'active', '2025-06-09 08:17:53', '2025-06-09 08:17:53', 19.07600000, 72.87770000),
(3, 'Rahul Kumar', 'rahul@example.com', 'default.png', '$2y$10$fYvtqI7v3c2.Tob1T/43LuNqAHYt4fXOynVSG5a9lXGD/uUC44a3K', 'Bangalore', '9988776655', 'active', '2025-06-09 08:17:53', '2025-06-12 10:48:07', 22.71956870, 75.85772580),
(4, 'Samkit Jain', 'samkitjain2809@gmail.com', 'default.png', '$2y$10$mjJX3auWxBZEa9tYrr6V1ujaA5RX9HXU4stpL.CcFtLySH4avkGZG', 'Ahmedabad', '7772020586', 'active', '2025-06-10 03:33:03', '2025-06-12 11:22:24', 22.71956870, 75.85772580);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `book_listings`
--
ALTER TABLE `book_listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_board` (`board_id`),
  ADD KEY `fk_subject` (`subject_id`);

--
-- Indexes for table `digital_files`
--
ALTER TABLE `digital_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `file_feedback`
--
ALTER TABLE `file_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mimes`
--
ALTER TABLE `mimes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reported_content`
--
ALTER TABLE `reported_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `boards`
--
ALTER TABLE `boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `book_listings`
--
ALTER TABLE `book_listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `digital_files`
--
ALTER TABLE `digital_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_feedback`
--
ALTER TABLE `file_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `mimes`
--
ALTER TABLE `mimes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reported_content`
--
ALTER TABLE `reported_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `book_listings`
--
ALTER TABLE `book_listings`
  ADD CONSTRAINT `book_listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_board` FOREIGN KEY (`board_id`) REFERENCES `boards` (`id`),
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `digital_files`
--
ALTER TABLE `digital_files`
  ADD CONSTRAINT `digital_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `digital_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_feedback`
--
ALTER TABLE `file_feedback`
  ADD CONSTRAINT `file_feedback_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `digital_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reported_content`
--
ALTER TABLE `reported_content`
  ADD CONSTRAINT `reported_content_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reported_content_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
