-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 04:22 PM
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
(1, 'admin', '$2y$10$Q9oTT87zOSUl1wat6LkUSuf/F2ehpzSBl4NOfAMtkp1uEqKYxZGmi', 'super_admin', '2025-06-18 14:06:06', '2025-06-02 13:30:16');

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
(1, 1, 'Test Upload', 'Available', 'http://localhost/eduvault/uploads/images/68524b2a09788.jpg', 'Ahmedabad', '2025-06-18 05:14:18', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`) VALUES
(9, 'Bachelor of Architecture (BArch)'),
(1, 'Bachelor of Arts (BA)'),
(7, 'Bachelor of Business Administration (BBA)'),
(3, 'Bachelor of Commerce (BCom)'),
(6, 'Bachelor of Computer Applications (BCA)'),
(10, 'Bachelor of Design (BDes)'),
(11, 'Bachelor of Education (BEd)'),
(5, 'Bachelor of Engineering (BE)'),
(8, 'Bachelor of Fine Arts (BFA)'),
(15, 'Bachelor of Hotel Management (BHM)'),
(16, 'Bachelor of Journalism and Mass Communication (BJMC)'),
(12, 'Bachelor of Laws (LLB)'),
(13, 'Bachelor of Pharmacy (BPharm)'),
(14, 'Bachelor of Physiotherapy (BPT)'),
(2, 'Bachelor of Science (BSc)'),
(4, 'Bachelor of Technology (BTech)'),
(19, 'BAMS (Bachelor of Ayurvedic Medicine and Surgery)'),
(18, 'BDS (Bachelor of Dental Surgery)'),
(20, 'BHMS (Bachelor of Homeopathic Medicine and Surgery)'),
(21, 'BSc Nursing'),
(45, 'Certificate in Data Science'),
(43, 'Certificate in Digital Marketing'),
(44, 'Certificate in Graphic Design'),
(42, 'Certificate in Web Development'),
(41, 'Diploma in Computer Applications'),
(39, 'Diploma in Elementary Education (D.El.Ed)'),
(36, 'Diploma in Engineering (Polytechnic)'),
(40, 'Diploma in Fashion Designing'),
(38, 'Diploma in Nursing'),
(37, 'Diploma in Pharmacy (DPharm)'),
(22, 'Master of Arts (MA)'),
(28, 'Master of Business Administration (MBA)'),
(24, 'Master of Commerce (MCom)'),
(27, 'Master of Computer Applications (MCA)'),
(30, 'Master of Design (MDes)'),
(31, 'Master of Education (MEd)'),
(26, 'Master of Engineering (ME)'),
(29, 'Master of Fine Arts (MFA)'),
(32, 'Master of Laws (LLM)'),
(33, 'Master of Pharmacy (MPharm)'),
(23, 'Master of Science (MSc)'),
(25, 'Master of Technology (MTech)'),
(17, 'MBBS (Bachelor of Medicine and Bachelor of Surgery)'),
(34, 'MDS (Master of Dental Surgery)'),
(47, 'MPhil (Master of Philosophy)'),
(35, 'PGDM (Post Graduate Diploma in Management)'),
(46, 'PhD (Doctor of Philosophy)');

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
  `verified` tinyint(1) NOT NULL DEFAULT 1,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `year_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `digital_files`
--

INSERT INTO `digital_files` (`id`, `user_id`, `title`, `description`, `file_path`, `verified`, `file_type`, `file_size`, `upload_date`, `subject_id`, `course_id`, `year_id`) VALUES
(1, 1, 'test upload', 'test', '../uploads/files/6852c545661b1.pdf', 1, 'pdf', '2.8', '2025-06-18 13:55:17', 9, 36, 1);

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
(1, 1, 1, '2025-06-18 13:55:38');

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
(1, 1, 1, 4, 'Test 2', '2025-06-18 14:00:52');

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
(1, 'Samkit_Jain', 'Jay@gmail.com', 'uploads/avatars/685250a33481e.png', '$2y$10$lG8nguVhBZKKAnpxOy5nV.8XNPGQRsSiUqopHTLoV1XpOWXFOfKaC', 'Vadodara', '7984651320', 'active', '2025-06-18 05:12:27', '2025-06-18 14:19:09', 23.09386970, 72.68163910),
(2, 'krish', 'krish@mail.com', 'default.png', '$2y$10$0hu1mjsfWEhIBXfYVP/DQe9FjdWywrRXMQOJqh086uSX5HLL.JN2e', 'Ahmedabad', '1234567890', 'active', '2025-06-18 14:18:25', '2025-06-18 14:18:39', 23.09386970, 72.68163910);

-- --------------------------------------------------------

--
-- Table structure for table `years`
--

CREATE TABLE `years` (
  `id` int(11) NOT NULL,
  `year` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `years`
--

INSERT INTO `years` (`id`, `year`) VALUES
(6, '1st Semester'),
(1, '1st Year'),
(7, '2nd Semester'),
(2, '2nd Year'),
(8, '3rd Semester'),
(3, '3rd Year'),
(9, '4th Semester'),
(4, '4th Year'),
(10, '5th Semester'),
(5, '5th Year'),
(11, '6th Semester'),
(12, '7th Semester'),
(13, '8th Semester'),
(14, 'Final Year');

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
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- Indexes for table `years`
--
ALTER TABLE `years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `digital_files`
--
ALTER TABLE `digital_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `file_feedback`
--
ALTER TABLE `file_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mimes`
--
ALTER TABLE `mimes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reported_content`
--
ALTER TABLE `reported_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `years`
--
ALTER TABLE `years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
