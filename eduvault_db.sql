-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 09, 2025 at 05:59 AM
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
(1, 'admin', '$2y$10$Q9oTT87zOSUl1wat6LkUSuf/F2ehpzSBl4NOfAMtkp1uEqKYxZGmi', 'super_admin', '2025-06-08 12:10:09', '2025-06-02 13:30:16');

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
(41, 1, 'My Bool', 'Available', '../images/books/6845646395593.png', 'Ahmedabad', '2025-06-08 10:22:27', 3, 5);

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
  `file_type` varchar(20) DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `digital_files`
--

INSERT INTO `digital_files` (`id`, `user_id`, `title`, `description`, `file_path`, `subject`, `course`, `year`, `file_type`, `upload_date`) VALUES
(2, 1, 'OOP Concepts', 'Object Oriented Programming overview', '../uploads/files/oop.docx', 'Computer Science', 'BCA', '2022', 'DOCX', '2025-06-01 12:44:43'),
(3, 1, 'Modern Physics', 'Quantum & relativity topics', 'files/physics.pdf', 'Physics', 'B.Sc', '2021', 'PDF', '2025-06-01 12:44:43'),
(4, 1, 'Organic Chemistry', 'Reactions and mechanisms', 'files/organic.pdf', 'Chemistry', 'B.Sc', '2023', 'PDF', '2025-06-01 12:44:43'),
(5, 1, 'World History', 'Notes on major world events', 'files/history.pptx', 'History', 'BA', '2020', 'PPTX', '2025-06-01 12:44:43'),
(6, 1, 'Data Structures', 'Stacks, Queues, Trees', 'files/dsa.pdf', 'Computer Science', 'B.Tech', '2023', 'PDF', '2025-06-01 12:44:43'),
(7, 1, 'Database Systems', 'SQL basics and normalization', 'files/dbms.docx', 'Computer Science', 'BCA', '2022', 'DOCX', '2025-06-01 12:44:43'),
(8, 1, 'Microeconomics', 'Demand and supply theory', 'files/economics.pdf', 'Economics', 'BBA', '2021', 'PDF', '2025-06-01 12:44:43'),
(9, 1, 'Statistics 101', 'Intro to probability and stats', 'files/statistics.pdf', 'Mathematics', 'B.Sc', '2023', 'PDF', '2025-06-01 12:44:43'),
(10, 1, 'Operating Systems', 'Processes, threads, scheduling', 'files/os.pptx', 'Computer Science', 'B.Tech', '2022', 'PPTX', '2025-06-01 12:44:43'),
(11, 1, 'Human Psychology', 'Behavior and cognition', 'files/psychology.pdf', 'Psychology', 'BA', '2020', 'PDF', '2025-06-01 12:44:43'),
(12, 1, 'Indian Constitution', 'Fundamental rights and duties', 'files/law.pdf', 'Political Science', 'BA', '2021', 'PDF', '2025-06-01 12:44:43'),
(13, 1, 'Compiler Design', 'Parsing, tokens, grammars', 'files/compiler.pdf', 'Computer Science', 'M.Tech', '2023', 'PDF', '2025-06-01 12:44:43'),
(14, 1, 'Research Methodology', 'Scientific research techniques', 'files/research.pdf', 'Science', 'M.Sc', '2022', 'PDF', '2025-06-01 12:44:43'),
(15, 1, 'Philosophical Thoughts', 'Western and Eastern philosophy', 'files/philosophy.docx', 'Philosophy', 'BA', '2020', 'DOCX', '2025-06-01 12:44:43');

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
(2, 2, 1, '2025-06-01 12:44:50'),
(3, 3, 1, '2025-06-01 12:44:50'),
(4, 4, 1, '2025-06-01 12:44:50'),
(5, 5, 1, '2025-06-01 12:44:50'),
(6, 6, 1, '2025-06-01 12:44:50'),
(7, 7, 1, '2025-06-01 12:44:50'),
(8, 8, 1, '2025-06-01 12:44:50'),
(9, 9, 1, '2025-06-01 12:44:50'),
(10, 10, 1, '2025-06-01 12:44:50'),
(11, 11, 1, '2025-06-01 12:44:50'),
(12, 12, 1, '2025-06-01 12:44:50'),
(13, 13, 1, '2025-06-01 12:44:50'),
(14, 14, 1, '2025-06-01 12:44:50'),
(15, 15, 1, '2025-06-01 12:44:50'),
(18, 2, 1, '2025-06-02 05:00:06'),
(20, 2, 1, '2025-06-08 11:23:41'),
(21, 2, 1, '2025-06-08 11:33:24'),
(22, 2, 1, '2025-06-08 11:36:54'),
(23, 2, 1, '2025-06-08 11:37:08'),
(24, 2, 1, '2025-06-08 11:38:16'),
(25, 2, 1, '2025-06-08 11:39:41'),
(26, 2, 1, '2025-06-08 11:40:01'),
(27, 2, 1, '2025-06-08 11:40:52'),
(28, 2, 1, '2025-06-08 11:41:05'),
(29, 2, 1, '2025-06-08 11:41:20'),
(30, 2, 1, '2025-06-08 12:26:14');

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
(2, 2, 1, 4, 'Good explanations', '2025-06-01 12:44:54'),
(3, 3, 1, 5, 'Excellent content', '2025-06-01 12:44:54'),
(4, 4, 1, 3, 'Needs some corrections', '2025-06-01 12:44:54'),
(5, 5, 1, 4, 'Useful for revisions', '2025-06-01 12:44:54'),
(6, 6, 1, 5, 'Perfectly structured', '2025-06-01 12:44:54'),
(7, 7, 1, 2, 'Could use better formatting', '2025-06-01 12:44:54'),
(8, 8, 1, 5, 'Really informative', '2025-06-01 12:44:54'),
(9, 9, 1, 3, 'Decent but outdated', '2025-06-01 12:44:54'),
(10, 10, 1, 4, 'Well summarized', '2025-06-01 12:44:54'),
(11, 11, 1, 4, 'Nice for beginners', '2025-06-01 12:44:54'),
(12, 12, 1, 5, 'Concise and clear', '2025-06-01 12:44:54'),
(13, 13, 1, 4, 'Great detail', '2025-06-01 12:44:54'),
(14, 14, 1, 5, 'Highly recommended', '2025-06-01 12:44:54'),
(15, 15, 1, 3, 'Average quality', '2025-06-01 12:44:54');

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
  `admin_id` int(11) DEFAULT NULL,
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
(4, 'allowed_file_types', 'pdf,doc,docx,ppt,pptx', 'Allowed file extensions', NULL, '2025-06-02 13:30:16'),
(5, 'items_per_page', '12', 'Number of items to show per page', NULL, '2025-06-02 13:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
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

INSERT INTO `users` (`id`, `name`, `email`, `password`, `location`, `phone`, `status`, `created_at`, `last_active`, `latitude`, `longitude`) VALUES
(1, 'Samkit Jain', 'samkitjain2809@gmail.com', '$2y$10$3s1LHsivZ/y3archrvIUv.po80WFqwfH7zZ8bWG5TADhkHNRc5K0y', 'Ahmedabad', '8200700139', 'active', '2025-05-31 15:39:16', '2025-06-09 03:52:43', 22.71956870, 75.85772580),
(3, 'Ravi Verma', 'ravi.verma@example.com', '$2y$10$c2CQjSIQXG4v6.oqXYxWluAfYzY/wpA5.zkZOrHIlfedOwLGWkK.G', 'Lucknow', '9876543211', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(4, 'Meena Shah', 'meena.shah@example.com', '$2y$10$Xqe1hLcZ42oK02SvDpo0M.JDbB0/OUtQrgp6pgEdtJd11e4lkPl9K', 'Jaipur', '9876543212', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(5, 'Rahul Kapoor', 'rahul.kapoor@example.com', '$2y$10$5K6CfmOfdSOg7u3ZEEeOF.2o2xUzNLiFfMPikXzwUVJVOM3kd3kru', 'Delhi', '9876543213', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(6, 'Tina Dsouza', 'tina.dsouza@example.com', '$2y$10$oSOM/XqHzzZIAwUdYmoXfuk0gtI7X1uT0tZ68s/b1IE8T4EkEDPDm', 'Mumbai', '9876543214', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(7, 'Karan Mehta', 'karan.mehta@example.com', '$2y$10$Gg8cdbHcWYljlqRTXauM6eik7dbbjGcTDc6lbMcChJoZR7Gc3WOSC', 'Pune', '9876543215', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(8, 'Deepa Reddy', 'deepa.reddy@example.com', '$2y$10$5ARWwHzQYI2xDJfMYfjO7e7b49RwHBKN9DICO4hN9p08FG2ismxa.', 'Hyderabad', '9876543216', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(10, 'Sneha Nair', 'sneha.nair@example.com', '$2y$10$5znB6Qrkj8Czr2qGCKgINeX976.NiiL3.t9GPaqMS3LO1VkS/u74K', 'Chennai', '9876543218', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(11, 'Amit Kulkarni', 'amit.kulkarni@example.com', '$2y$10$uw8jdI.GEf5iIIkcYl0zNe9UFcNCbwNtZwLdcLBFvsUXwTxasI85i', 'Nagpur', '9876543219', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(12, 'Neha Gupta', 'neha@gmail.com', '$2y$10$yOlaZ1iqBCfGYbVthxwcSOrrMmjSRFag6kpBuXfa0QuJ.ObOVLA16', 'Bhopal', '9876543220', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(13, 'Pooja Bhatt', 'pooja.bhatt@example.com', '$2y$10$sqwGyl7ZwEje5uF3XZzxCO9NxqShkbgSHN3g2B6/8bDk02Ih5BzZ2', 'Patna', '9876543221', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(14, 'Vikram Singh', 'vikram.singh@example.com', '$2y$10$0OiG4lf1UOnHeu9hMDMnIemRE4.2cF4N2f7DwpQUTt4zS01UU7gTK', 'Varanasi', '9876543222', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(15, 'Harshita Rana', 'harshita.rana@example.com', 'hashed_password_14', 'Chandigarh', '9876543223', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(16, 'Arjun Das', 'arjun.das@example.com', 'hashed_password_15', 'Kolkata', '9876543224', 'active', '2025-06-01 12:44:59', '2025-06-09 03:52:43', NULL, NULL),
(17, 'Jay ', 'Jay@gmail.com', '$2y$10$UpehAoopPoB1LcJnjVBz7e5gIdo31vemIxgfvHwO.rUkdWHQEDki2', 'Vadodara', '9876456252', 'active', '2025-06-02 05:25:59', '2025-06-09 03:52:43', 23.02250500, 72.57136210);

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
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `digital_files`
--
ALTER TABLE `digital_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `file_feedback`
--
ALTER TABLE `file_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reported_content`
--
ALTER TABLE `reported_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
