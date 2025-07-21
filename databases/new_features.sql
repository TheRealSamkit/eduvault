-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 05:30 PM
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
-- Database: `new_features`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchFiles` (IN `p_search_query` VARCHAR(255), IN `p_subject_id` INT, IN `p_course_id` INT, IN `p_year_id` INT, IN `p_file_type` VARCHAR(20), IN `p_sort_by` ENUM('relevance','popularity','recent','rating'), IN `p_limit` INT, IN `p_offset` INT)   BEGIN
    DECLARE search_sql TEXT;
    
    SET search_sql = '
        SELECT 
            df.*,
            u.name as uploader_name,
            s.name as subject_name,
            c.name as course_name,
            y.year as year_name,
            MATCH(df.title, df.description, df.tags, df.keywords) AGAINST (? IN BOOLEAN MODE) as relevance_score
        FROM digital_files df
        LEFT JOIN users u ON df.user_id = u.id
        LEFT JOIN subjects s ON df.subject_id = s.id
        LEFT JOIN courses c ON df.course_id = c.id
        LEFT JOIN years y ON df.year_id = y.id
        WHERE df.status = "active" AND df.visibility = "public" AND df.verified = 1
    ';
    
    -- Add search condition if query provided
    IF p_search_query IS NOT NULL AND p_search_query != '' THEN
        SET search_sql = CONCAT(search_sql, ' AND MATCH(df.title, df.description, df.tags, df.keywords) AGAINST (? IN BOOLEAN MODE)');
    END IF;
    
    -- Add filters
    IF p_subject_id IS NOT NULL THEN
        SET search_sql = CONCAT(search_sql, ' AND df.subject_id = ', p_subject_id);
    END IF;
    
    IF p_course_id IS NOT NULL THEN
        SET search_sql = CONCAT(search_sql, ' AND df.course_id = ', p_course_id);
    END IF;
    
    IF p_year_id IS NOT NULL THEN
        SET search_sql = CONCAT(search_sql, ' AND df.year_id = ', p_year_id);
    END IF;
    
    IF p_file_type IS NOT NULL THEN
        SET search_sql = CONCAT(search_sql, ' AND df.file_type = "', p_file_type, '"');
    END IF;
    
    -- Add sorting
    CASE p_sort_by
        WHEN 'popularity' THEN
            SET search_sql = CONCAT(search_sql, ' ORDER BY df.download_count DESC, df.average_rating DESC');
        WHEN 'recent' THEN
            SET search_sql = CONCAT(search_sql, ' ORDER BY df.upload_date DESC');
        WHEN 'rating' THEN
            SET search_sql = CONCAT(search_sql, ' ORDER BY df.average_rating DESC, df.download_count DESC');
        ELSE
            SET search_sql = CONCAT(search_sql, ' ORDER BY relevance_score DESC, df.download_count DESC');
    END CASE;
    
    -- Add pagination
    SET search_sql = CONCAT(search_sql, ' LIMIT ', p_limit, ' OFFSET ', p_offset);
    
    SET @sql = search_sql;
    PREPARE stmt FROM @sql;
    
    IF p_search_query IS NOT NULL AND p_search_query != '' THEN
        EXECUTE stmt USING p_search_query;
    ELSE
        EXECUTE stmt;
    END IF;
    
    DEALLOCATE PREPARE stmt;
END$$

DELIMITER ;

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
  `slug` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 1,
  `file_type` varchar(20) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes for proper sorting',
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `year_id` int(11) DEFAULT NULL,
  `tags` text DEFAULT NULL COMMENT 'Comma-separated tags for flexible categorization',
  `content_hash` varchar(64) DEFAULT NULL COMMENT 'SHA256 hash for duplicate detection',
  `download_count` int(11) DEFAULT 0 COMMENT 'Cached download count for popularity ranking',
  `average_rating` decimal(3,2) DEFAULT NULL COMMENT 'Cached average rating from feedback',
  `status` enum('active','pending','rejected','archived') DEFAULT 'active' COMMENT 'File status',
  `visibility` enum('public','private','restricted') DEFAULT 'public' COMMENT 'File visibility level',
  `keywords` text DEFAULT NULL COMMENT 'Extracted keywords from file content',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last metadata update'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `digital_files`
--

INSERT INTO `digital_files` (`id`, `slug`, `user_id`, `title`, `description`, `file_path`, `verified`, `file_type`, `file_size`, `upload_date`, `subject_id`, `course_id`, `year_id`, `tags`, `content_hash`, `download_count`, `average_rating`, `status`, `visibility`, `keywords`, `last_updated`) VALUES
(4, 'test-pythonupload-by-me', 2, 'Test-pythonupload by me', 'hrllo', '../uploads/files/686d46c1c828d.txt', 1, 'txt', 1010, '2025-07-08 16:26:41', 4, 9, 14, 'text, tag,my', '25ebad24e36c2f93e4c578818ee99520acb4a692e5ad58362a28f416ecfbc621', 4, NULL, 'active', 'public', 'test, pythonupload, me', '2025-07-10 13:19:35'),
(5, 'test-file-access-control', 1, 'Test File access control', '0', '../uploads/files/686e6fe8b4a6e.jpg', 1, 'jpg', 129268, '2025-07-09 13:34:32', 3, 9, 14, 'Test', 'bd65112e569f8f62a848425d139e6a4601964561874b793ca72b6f0a3ace26af', 1, NULL, 'active', 'public', 'test, file, access, control', '2025-07-10 12:53:39'),
(6, 'test-pdf-', 1, 'test pdf ', '0', '../uploads/files/686e6ffabe8cd.pdf', 1, 'pdf', 2296724, '2025-07-09 13:34:50', 4, 9, 14, 'text', '753082440fa5ffaa1548f9d50c18253c44235fb9db2f0b756dd8ae7ccb0f12c0', 0, NULL, 'active', 'public', 'test, pdf, text', '2025-07-09 13:34:50'),
(7, 'testing-upload', 1, 'Testing UPload', 'This just a test for the upload so i can ensure everything is working properly', '../uploads/files/68724cc00d0da.jpg', 1, 'jpg', 3883089, '2025-07-12 11:53:36', 4, 9, 14, 'text,duplicate,non,comprihensive', '1a7ba188e69a07f526fbd6c879023a5aeb5135b1d8590db74bcc3f970d5c1bb3', 0, NULL, 'active', 'public', 'upload, testing, just, test, so, i, ensure, everything, working, properly', '2025-07-12 11:53:36');

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
(2, 4, 2, '2025-07-08 16:26:52'),
(3, 4, 1, '2025-07-09 13:22:47'),
(4, 5, 1, '2025-07-10 12:53:39'),
(5, 4, 1, '2025-07-10 13:19:27'),
(6, 4, 1, '2025-07-10 13:19:35');

--
-- Triggers `downloads`
--
DELIMITER $$
CREATE TRIGGER `update_download_count` AFTER INSERT ON `downloads` FOR EACH ROW BEGIN
    UPDATE digital_files 
    SET download_count = download_count + 1 
    WHERE id = NEW.file_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `file_bookmarks`
--

CREATE TABLE `file_bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `bookmarked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file_bookmarks`
--

INSERT INTO `file_bookmarks` (`id`, `user_id`, `file_id`, `bookmarked_at`) VALUES
(1, 1, 5, '2025-07-10 19:08:39'),
(2, 1, 6, '2025-07-10 19:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `file_downloads`
--

CREATE TABLE `file_downloads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `downloaded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Triggers `file_feedback`
--
DELIMITER $$
CREATE TRIGGER `update_average_rating` AFTER INSERT ON `file_feedback` FOR EACH ROW BEGIN
    UPDATE digital_files 
    SET average_rating = (
        SELECT AVG(rating) 
        FROM file_feedback 
        WHERE file_id = NEW.file_id
    )
    WHERE id = NEW.file_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_average_rating_on_delete` AFTER DELETE ON `file_feedback` FOR EACH ROW BEGIN
    UPDATE digital_files 
    SET average_rating = (
        SELECT AVG(rating) 
        FROM file_feedback 
        WHERE file_id = OLD.file_id
    )
    WHERE id = OLD.file_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_average_rating_on_update` AFTER UPDATE ON `file_feedback` FOR EACH ROW BEGIN
    UPDATE digital_files 
    SET average_rating = (
        SELECT AVG(rating) 
        FROM file_feedback 
        WHERE file_id = NEW.file_id
    )
    WHERE id = NEW.file_id;
END
$$
DELIMITER ;

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
-- Table structure for table `search_analytics`
--

CREATE TABLE `search_analytics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `search_query` varchar(255) NOT NULL,
  `search_filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Applied filters as JSON' CHECK (json_valid(`search_filters`)),
  `results_count` int(11) DEFAULT 0,
  `clicked_file_id` int(11) DEFAULT NULL,
  `search_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search_analytics`
--

INSERT INTO `search_analytics` (`id`, `user_id`, `search_query`, `search_filters`, `results_count`, `clicked_file_id`, `search_date`, `ip_address`) VALUES
(1, 1, '', '{\"file_type\":\"pdf\"}', 1, NULL, '2025-07-07 14:29:08', '::1'),
(2, 1, '', '{\"file_type\":\"pdf\"}', 1, NULL, '2025-07-07 14:30:44', '::1'),
(3, 1, '', '{\"file_type\":\"pdf\"}', 1, NULL, '2025-07-07 14:30:47', '::1'),
(4, 1, '', '{\"tags\":\"text\"}', 1, NULL, '2025-07-07 14:53:35', '::1'),
(5, 1, '', '{\"tags\":\"test\"}', 0, NULL, '2025-07-07 14:53:41', '::1'),
(6, 1, 'book', '[]', 0, NULL, '2025-07-07 15:13:29', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `search_suggestions`
--

CREATE TABLE `search_suggestions` (
  `id` int(11) NOT NULL,
  `suggestion` varchar(255) NOT NULL,
  `category` enum('subject','course','topic','general') DEFAULT 'general',
  `popularity_score` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `search_suggestions`
--

INSERT INTO `search_suggestions` (`id`, `suggestion`, `category`, `popularity_score`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics notes', 'subject', 10, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(2, 'Physics practical', 'subject', 8, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(3, 'Chemistry lab manual', 'subject', 7, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(4, 'Computer Science projects', 'subject', 9, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(5, 'Engineering drawing', 'course', 6, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(6, 'CBSE question papers', 'general', 12, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(7, 'Previous year papers', 'general', 11, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(8, 'Study material', 'general', 8, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(9, 'Lecture notes', 'general', 7, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(10, 'Assignment solutions', 'general', 9, '2025-07-07 13:55:35', '2025-07-07 13:55:35'),
(11, 'book', 'general', 1, '2025-07-07 15:13:29', '2025-07-07 15:13:29');

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
(5, 'items_per_page', '20', 'Number of items to show per page', NULL, '2025-07-07 13:55:35'),
(7, 'max_image_size', '2097125', 'maximum image size in bytes (2MB)', NULL, '2025-06-10 09:02:21'),
(8, 'allowed_image_types', 'jpg,png,jpeg', 'Allowed image extensions', NULL, '2025-06-10 08:53:14'),
(9, 'search_results_per_page', '15', 'Number of search results per page', NULL, '2025-07-07 13:55:35'),
(10, 'enable_search_analytics', '1', 'Enable search analytics tracking', NULL, '2025-07-07 13:55:35'),
(11, 'search_suggestions_limit', '10', 'Maximum number of search suggestions to show', NULL, '2025-07-07 13:55:35'),
(12, 'fulltext_min_word_length', '3', 'Minimum word length for full-text search', NULL, '2025-07-07 13:55:35'),
(13, 'search_cache_duration', '3600', 'Search results cache duration in seconds', NULL, '2025-07-07 13:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `avatar_path` varchar(255) NOT NULL DEFAULT 'uploads/avatars/default.png',
  `password` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `status` enum('blocked','active') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_active` timestamp NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tokens` int(11) NOT NULL DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar_path`, `password`, `location`, `phone`, `status`, `created_at`, `last_active`, `latitude`, `longitude`, `tokens`) VALUES
(1, 'Samkit Jain', 'samkitjain2809@gmail.com', 'https://lh3.googleusercontent.com/a/ACg8ocKvoATr3qz2h6pnYpVpdRXlTXppmuqrPniqkY21syT3gkGDRV2I=s96-c', '$2y$10$NymwfhWeo9Tdo3FkWjjyp.JXJzURehF/fFM..NaBq1mHUfRp/Vmla', NULL, NULL, 'active', '2025-07-07 14:11:16', '2025-07-07 14:11:16', NULL, NULL, 1),
(2, 'user1', 'user@mail.com', 'uploads/avatars/default.png', '$2y$10$Wi6YQ0vr9BXvdB2r2lv.IOHfOBo.9FubT7IfWzkVyCCm0geMhoEPO', NULL, NULL, 'active', '2025-07-08 16:00:18', '2025-07-08 16:00:27', NULL, NULL, 15);

-- --------------------------------------------------------

--
-- Table structure for table `user_file_access`
--

CREATE TABLE `user_file_access` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `accessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_file_access`
--

INSERT INTO `user_file_access` (`id`, `user_id`, `file_id`, `accessed_at`) VALUES
(1, 1, 5, '2025-07-09 16:11:01'),
(2, 1, 4, '2025-07-10 13:19:27'),
(3, 1, 6, '2025-07-10 13:45:34');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_popular_files`
-- (See below for the actual view)
--
CREATE TABLE `v_popular_files` (
`id` int(11)
,`user_id` int(11)
,`title` varchar(255)
,`description` text
,`file_path` varchar(255)
,`verified` tinyint(1)
,`file_type` varchar(20)
,`file_size` bigint(20)
,`upload_date` timestamp
,`subject_id` int(11)
,`course_id` int(11)
,`year_id` int(11)
,`tags` text
,`content_hash` varchar(64)
,`download_count` int(11)
,`average_rating` decimal(3,2)
,`status` enum('active','pending','rejected','archived')
,`visibility` enum('public','private','restricted')
,`keywords` text
,`last_updated` timestamp
,`uploader_name` varchar(100)
,`uploader_avatar` varchar(255)
,`subject_name` varchar(100)
,`course_name` varchar(100)
,`year_name` varchar(100)
,`total_downloads` int(11)
,`avg_rating` decimal(3,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_recent_files`
-- (See below for the actual view)
--
CREATE TABLE `v_recent_files` (
`id` int(11)
,`user_id` int(11)
,`title` varchar(255)
,`description` text
,`file_path` varchar(255)
,`verified` tinyint(1)
,`file_type` varchar(20)
,`file_size` bigint(20)
,`upload_date` timestamp
,`subject_id` int(11)
,`course_id` int(11)
,`year_id` int(11)
,`tags` text
,`content_hash` varchar(64)
,`download_count` int(11)
,`average_rating` decimal(3,2)
,`status` enum('active','pending','rejected','archived')
,`visibility` enum('public','private','restricted')
,`keywords` text
,`last_updated` timestamp
,`uploader_name` varchar(100)
,`uploader_avatar` varchar(255)
,`subject_name` varchar(100)
,`course_name` varchar(100)
,`year_name` varchar(100)
);

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

-- --------------------------------------------------------

--
-- Structure for view `v_popular_files`
--
DROP TABLE IF EXISTS `v_popular_files`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_popular_files`  AS SELECT `df`.`id` AS `id`, `df`.`user_id` AS `user_id`, `df`.`title` AS `title`, `df`.`description` AS `description`, `df`.`file_path` AS `file_path`, `df`.`verified` AS `verified`, `df`.`file_type` AS `file_type`, `df`.`file_size` AS `file_size`, `df`.`upload_date` AS `upload_date`, `df`.`subject_id` AS `subject_id`, `df`.`course_id` AS `course_id`, `df`.`year_id` AS `year_id`, `df`.`tags` AS `tags`, `df`.`content_hash` AS `content_hash`, `df`.`download_count` AS `download_count`, `df`.`average_rating` AS `average_rating`, `df`.`status` AS `status`, `df`.`visibility` AS `visibility`, `df`.`keywords` AS `keywords`, `df`.`last_updated` AS `last_updated`, `u`.`name` AS `uploader_name`, `u`.`avatar_path` AS `uploader_avatar`, `s`.`name` AS `subject_name`, `c`.`name` AS `course_name`, `y`.`year` AS `year_name`, coalesce(`df`.`download_count`,0) AS `total_downloads`, coalesce(`df`.`average_rating`,0) AS `avg_rating` FROM ((((`digital_files` `df` left join `users` `u` on(`df`.`user_id` = `u`.`id`)) left join `subjects` `s` on(`df`.`subject_id` = `s`.`id`)) left join `courses` `c` on(`df`.`course_id` = `c`.`id`)) left join `years` `y` on(`df`.`year_id` = `y`.`id`)) WHERE `df`.`status` = 'active' AND `df`.`visibility` = 'public' AND `df`.`verified` = 1 ORDER BY `df`.`download_count` DESC, `df`.`average_rating` DESC, `df`.`upload_date` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_recent_files`
--
DROP TABLE IF EXISTS `v_recent_files`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_recent_files`  AS SELECT `df`.`id` AS `id`, `df`.`user_id` AS `user_id`, `df`.`title` AS `title`, `df`.`description` AS `description`, `df`.`file_path` AS `file_path`, `df`.`verified` AS `verified`, `df`.`file_type` AS `file_type`, `df`.`file_size` AS `file_size`, `df`.`upload_date` AS `upload_date`, `df`.`subject_id` AS `subject_id`, `df`.`course_id` AS `course_id`, `df`.`year_id` AS `year_id`, `df`.`tags` AS `tags`, `df`.`content_hash` AS `content_hash`, `df`.`download_count` AS `download_count`, `df`.`average_rating` AS `average_rating`, `df`.`status` AS `status`, `df`.`visibility` AS `visibility`, `df`.`keywords` AS `keywords`, `df`.`last_updated` AS `last_updated`, `u`.`name` AS `uploader_name`, `u`.`avatar_path` AS `uploader_avatar`, `s`.`name` AS `subject_name`, `c`.`name` AS `course_name`, `y`.`year` AS `year_name` FROM ((((`digital_files` `df` left join `users` `u` on(`df`.`user_id` = `u`.`id`)) left join `subjects` `s` on(`df`.`subject_id` = `s`.`id`)) left join `courses` `c` on(`df`.`course_id` = `c`.`id`)) left join `years` `y` on(`df`.`year_id` = `y`.`id`)) WHERE `df`.`status` = 'active' AND `df`.`visibility` = 'public' AND `df`.`verified` = 1 ORDER BY `df`.`upload_date` DESC ;

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
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_digital_files_subject` (`subject_id`),
  ADD KEY `fk_digital_files_course` (`course_id`),
  ADD KEY `fk_digital_files_year` (`year_id`),
  ADD KEY `idx_search_filters` (`status`,`visibility`,`subject_id`,`course_id`,`year_id`,`file_type`),
  ADD KEY `idx_popularity_ranking` (`download_count`,`average_rating`,`upload_date`),
  ADD KEY `idx_user_uploads` (`user_id`,`status`,`upload_date`),
  ADD KEY `idx_content_hash` (`content_hash`),
  ADD KEY `idx_file_type_size` (`file_type`,`file_size`),
  ADD KEY `idx_verified_active` (`verified`,`status`,`visibility`);
ALTER TABLE `digital_files` ADD FULLTEXT KEY `ft_search_content` (`title`,`description`,`tags`,`keywords`);

--
-- Indexes for table `downloads`
--
ALTER TABLE `downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_downloads_file_date` (`file_id`,`downloaded_at`),
  ADD KEY `idx_downloads_user_date` (`user_id`,`downloaded_at`);

--
-- Indexes for table `file_bookmarks`
--
ALTER TABLE `file_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`file_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `file_downloads`
--
ALTER TABLE `file_downloads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`file_id`,`downloaded_at`);

--
-- Indexes for table `file_feedback`
--
ALTER TABLE `file_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_feedback_file_rating` (`file_id`,`rating`,`created_at`),
  ADD KEY `idx_feedback_user_date` (`user_id`,`created_at`);

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
-- Indexes for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_search_query` (`search_query`),
  ADD KEY `idx_search_date` (`search_date`),
  ADD KEY `idx_user_searches` (`user_id`,`search_date`),
  ADD KEY `clicked_file_id` (`clicked_file_id`);

--
-- Indexes for table `search_suggestions`
--
ALTER TABLE `search_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_suggestion` (`suggestion`),
  ADD KEY `idx_suggestions_category` (`category`,`popularity_score`),
  ADD KEY `idx_suggestions_popularity` (`popularity_score`);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_location` (`location`),
  ADD KEY `idx_users_active_status` (`status`,`last_active`);

--
-- Indexes for table `user_file_access`
--
ALTER TABLE `user_file_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`file_id`),
  ADD KEY `file_id` (`file_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `digital_files`
--
ALTER TABLE `digital_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `downloads`
--
ALTER TABLE `downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_bookmarks`
--
ALTER TABLE `file_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `file_downloads`
--
ALTER TABLE `file_downloads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `search_analytics`
--
ALTER TABLE `search_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `search_suggestions`
--
ALTER TABLE `search_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_file_access`
--
ALTER TABLE `user_file_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `digital_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_digital_files_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_digital_files_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_digital_files_year` FOREIGN KEY (`year_id`) REFERENCES `years` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `downloads`
--
ALTER TABLE `downloads`
  ADD CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `digital_files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `file_bookmarks`
--
ALTER TABLE `file_bookmarks`
  ADD CONSTRAINT `file_bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `file_bookmarks_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `digital_files` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `search_analytics`
--
ALTER TABLE `search_analytics`
  ADD CONSTRAINT `search_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `search_analytics_ibfk_2` FOREIGN KEY (`clicked_file_id`) REFERENCES `digital_files` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_file_access`
--
ALTER TABLE `user_file_access`
  ADD CONSTRAINT `user_file_access_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_file_access_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `digital_files` (`id`) ON DELETE CASCADE;

-- User preferences table
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `preference_key` VARCHAR(50) NOT NULL,
  `preference_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_preference` (`user_id`, `preference_key`),
  INDEX `idx_user_preferences` (`user_id`, `preference_key`)
);

-- Insert default preferences for existing users
INSERT INTO `user_preferences` (`user_id`, `preference_key`, `preference_value`) VALUES
(1, 'notify_downloads', '1'),
(1, 'notify_downloads_threshold', '10'),
(1, 'notify_feedback', '1'),
(1, 'notify_tokens', '1'),
(1, 'newsletter', '1'),
(1, 'allow_feedback', '1'),
(1, 'theme', 'auto'),
(1, 'email_notifications', '1'),
(1, 'push_notifications', '1'),
(1, 'privacy_level', 'public'),
(1, 'search_history', '1'),
(1, 'activity_visibility', 'public'),
(2, 'notify_downloads', '1'),
(2, 'notify_downloads_threshold', '10'),
(2, 'notify_feedback', '1'),
(2, 'notify_tokens', '1'),
(2, 'newsletter', '1'),
(2, 'allow_feedback', '1'),
(2, 'theme', 'auto'),
(2, 'email_notifications', '1'),
(2, 'push_notifications', '1'),
(2, 'privacy_level', 'public'),
(2, 'search_history', '1'),
(2, 'activity_visibility', 'public');

-- User activity history
CREATE TABLE IF NOT EXISTS `user_activity` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `activity_type` ENUM('search','view','preview','download') NOT NULL,
  `file_id` INT DEFAULT NULL,
  `search_query` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`file_id`) REFERENCES `digital_files`(`id`) ON DELETE SET NULL
);

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('download','feedback','system','token','file_approved','file_rejected','bookmark','report_resolved') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `related_file_id` INT DEFAULT NULL,
  `related_user_id` INT DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`related_file_id`) REFERENCES `digital_files`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`related_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_read` (`user_id`, `is_read`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_type` (`type`)
);

-- Insert some sample notifications for testing
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `related_file_id`, `is_read`) VALUES
(1, 'download', 'File Downloaded', 'Your file "Test File access control" has been downloaded by another user.', 5, 0),
(1, 'feedback', 'New Feedback Received', 'You received a 5-star rating on your file "Test-pythonupload by me".', 4, 0),
(1, 'system', 'Welcome to EduVault', 'Thank you for joining EduVault! Start sharing your educational resources.', NULL, 0),
(2, 'system', 'Welcome to EduVault', 'Thank you for joining EduVault! Start sharing your educational resources.', NULL, 0);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
