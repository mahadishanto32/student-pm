-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 03:00 PM
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
-- Database: `student_pms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `meeting_date_and_time` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `supervisor_note` text DEFAULT NULL,
  `platform` enum('physical','online') NOT NULL DEFAULT 'physical',
  `type` enum('present','completed','upcoming') NOT NULL DEFAULT 'upcoming',
  `tentative_next_meeting_date_and_time` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `project_id`, `meeting_date_and_time`, `title`, `description`, `supervisor_note`, `platform`, `type`, `tentative_next_meeting_date_and_time`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-09-23 14:00:00', 'Indtroduction Meeting', 'Test', 'ss', 'physical', 'present', '2026-09-26 12:00:00', '2026-09-23 00:07:46', '2026-09-23 00:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_20_065420_fix_users_password_column', 2),
(5, '2026_09_20_162938_add_role_to_users_table', 3),
(6, '2026_09_22_072615_create_projects_table', 4),
(7, '2026_09_22_073229_create_project_user_table', 5),
(8, '2026_09_22_073643_create_projects_table', 6),
(9, '2026_09_22_125423_create_project_books_table', 7),
(10, '2026_09_22_125434_create_project_book_chapters_table', 7),
(11, '2026_09_22_174825_create_meetings_table', 8),
(12, '2026_09_23_112342_create_project_milestones_table', 9),
(13, '2026_09_23_112417_create_milestone_tasks_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `milestone_tasks`
--

CREATE TABLE `milestone_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_milestone_id` bigint(20) UNSIGNED NOT NULL,
  `key_points` text NOT NULL,
  `document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `milestone_tasks`
--

INSERT INTO `milestone_tasks` (`id`, `project_milestone_id`, `key_points`, `document`, `created_at`, `updated_at`) VALUES
(1, 1, 'vdsvd', 'uploads/tasks/1790163788_6ab3bb4c303d5.jpg', '2026-09-23 05:43:08', '2026-09-23 05:43:08'),
(2, 1, 'test', 'uploads/tasks/1790163788_6ab3bb4c373a3.docx', '2026-09-23 05:43:08', '2026-09-23 05:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_number` varchar(255) NOT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `project_topic` varchar(255) DEFAULT NULL,
  `short_overview` text DEFAULT NULL,
  `assigned_teacher` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date NOT NULL,
  `tentative_end_date` date DEFAULT NULL,
  `status` enum('approved','pending','working','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `group_number`, `project_name`, `project_topic`, `short_overview`, `assigned_teacher`, `start_date`, `tentative_end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, '01', 'Student Project Management', 'PM', 'Student Project Management System', 8, '2026-09-22', '2026-12-11', 'pending', '2026-09-22 03:01:38', '2026-09-22 03:52:22'),
(2, '02', NULL, NULL, NULL, 3, '2026-09-21', NULL, 'pending', '2026-09-22 03:20:19', '2026-09-22 03:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `project_books`
--

CREATE TABLE `project_books` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('approved','pending','working','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_books`
--

INSERT INTO `project_books` (`id`, `project_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'pending', '2026-09-22 07:16:27', '2026-09-22 07:16:27');

-- --------------------------------------------------------

--
-- Table structure for table `project_book_chapters`
--

CREATE TABLE `project_book_chapters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_book_id` bigint(20) UNSIGNED NOT NULL,
  `chapter_no` int(10) UNSIGNED NOT NULL,
  `chapter_title` varchar(255) NOT NULL,
  `chapter_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_book_chapters`
--

INSERT INTO `project_book_chapters` (`id`, `project_book_id`, `chapter_no`, `chapter_title`, `chapter_description`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 'Introduction', 'Intro', '2026-09-22 07:17:57', '2026-09-22 07:17:57'),
(3, 1, 2, 'Literature Review', 'Literature Review Chapter', '2026-09-22 07:17:57', '2026-09-22 07:17:57');

-- --------------------------------------------------------

--
-- Table structure for table `project_milestones`
--

CREATE TABLE `project_milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `tentative_time` timestamp NULL DEFAULT NULL,
  `supervisor_note` text DEFAULT NULL,
  `status` enum('pending','need_correction','completed','rejected') NOT NULL DEFAULT 'pending',
  `done_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_milestones`
--

INSERT INTO `project_milestones` (`id`, `project_id`, `title`, `tentative_time`, `supervisor_note`, `status`, `done_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Project Setup', '2026-09-24 11:41:00', 'Good', 'pending', 6, '2026-09-23 05:43:08', '2026-09-23 05:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `project_user`
--

CREATE TABLE `project_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_user`
--

INSERT INTO `project_user` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2026-09-22 03:19:40', '2026-09-22 03:19:40'),
(2, 1, 7, '2026-09-22 03:19:40', '2026-09-22 03:19:40'),
(3, 2, 5, '2026-09-22 03:20:19', '2026-09-22 03:20:19'),
(4, 2, 4, '2026-09-22 03:20:19', '2026-09-22 03:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CwSEJL3809z54rPcQ1JmmAJTeaFtqbxeQ32PzpUg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiS25weWVoZjdRNndMSlBKRTIyREpuVVhmNHB3SVUwQ1p4NTBmQTRzaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1790168363),
('Hja28HuJnf939hA7GaHmjK5VzpEfKkewEu2rKmR8', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUGxTbDJ3aktkQnVTZ0xKdjNwVHdqWWNTRE5LT2RYQUtBWDJQSHZ5WSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9wcm9qZWN0LWJvb2tzL2NyZWF0ZSI7czo1OiJyb3V0ZSI7czoyNjoiYWRtaW4ucHJvamVjdC1ib29rcy5jcmVhdGUiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1790086288),
('s4oN3AFRpo1Y8T5fFGn3QAGdlSwKjJc5boSqSUZb', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiR0VaMVRDb1ZGbHVCVUVQczlPZ1RUUW9sV1czZzBhcE5HbHZwUTdLbiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9zdHVkZW50cy9tZWV0aW5ncy8xIjtzOjU6InJvdXRlIjtzOjIxOiJzdHVkZW50Lm1lZXRpbmdzLnNob3ciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo2O30=', 1790146343);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Administrator', 'admin@spms.com', 'admin', '2026-09-21 10:15:48', '$2y$12$e22O6bscG9sOmK1I4J2rT.MmT68gWcClXmPkncMloX2WseJKIPLnW', NULL, '2026-09-20 00:57:15', '2026-09-22 03:02:14'),
(3, 'Test Teacher', 'teacher@spms.com', 'teacher', '2026-09-21 10:15:38', '$2y$12$cBdiyuIyGxE7X/qbYRmmG.PJb.m9rj/X.7cmNYJPBdgcrBwAaC2A.', NULL, '2026-09-20 10:40:17', '2026-09-21 10:15:38'),
(4, 'Test Student', 'student1@spms.com', 'student', '2026-09-21 10:15:30', '$2y$12$hTfNoZ5xy4LnUkJj1OjVXuu27gygQo3NASvUozKZkA1mYiaKElQ5C', NULL, '2026-09-20 10:42:37', '2026-09-21 10:15:58'),
(5, 'Student 2', 'student2@spms.com', 'student', '2026-09-21 10:17:13', '$2y$12$jpuBy/.9sp/Ac2nPnA.qu.Ck8S0K9/QvmUoayr9jFssqYa4GQomVS', NULL, '2026-09-21 10:16:39', '2026-09-21 10:17:13'),
(6, 'Sweety', 'sweety@spms.com', 'student', '2026-09-22 03:00:38', '$2y$12$DLxKwgP5ETbKqzNdMOFYP./NEhe8JRXxqosAEYInI41Ru8Mxk5Iou', NULL, '2026-09-22 02:58:04', '2026-09-22 03:00:38'),
(7, 'Tithi', 'tithi@spms.com', 'student', '2026-09-22 03:00:33', '$2y$12$TBZcAwsh4oURA9b2t/tMa.DiyYoK7qDRNBa2haUpUHAyDzvgz3kJS', NULL, '2026-09-22 02:59:22', '2026-09-22 03:00:33'),
(8, 'Poly Bhowmik', 'poly@spms.com', 'teacher', '2026-09-22 03:00:27', '$2y$12$EC6THEscDFUq1wRcAJQJR.jNkf7GPdtmaDYO1./HEhk6wSJPyCdIS', NULL, '2026-09-22 03:00:15', '2026-09-22 03:00:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meetings_project_id_foreign` (`project_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `milestone_tasks`
--
ALTER TABLE `milestone_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_tasks_project_milestone_id_foreign` (`project_milestone_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projects_group_number_unique` (`group_number`),
  ADD KEY `projects_assigned_teacher_foreign` (`assigned_teacher`);

--
-- Indexes for table `project_books`
--
ALTER TABLE `project_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_books_project_id_foreign` (`project_id`);

--
-- Indexes for table `project_book_chapters`
--
ALTER TABLE `project_book_chapters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_book_chapters_project_book_id_chapter_no_unique` (`project_book_id`,`chapter_no`);

--
-- Indexes for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_milestones_project_id_foreign` (`project_id`),
  ADD KEY `project_milestones_done_by_foreign` (`done_by`);

--
-- Indexes for table `project_user`
--
ALTER TABLE `project_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_user_project_id_user_id_unique` (`project_id`,`user_id`),
  ADD KEY `project_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `milestone_tasks`
--
ALTER TABLE `milestone_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_books`
--
ALTER TABLE `project_books`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_book_chapters`
--
ALTER TABLE `project_book_chapters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_milestones`
--
ALTER TABLE `project_milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_user`
--
ALTER TABLE `project_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meetings`
--
ALTER TABLE `meetings`
  ADD CONSTRAINT `meetings_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `milestone_tasks`
--
ALTER TABLE `milestone_tasks`
  ADD CONSTRAINT `milestone_tasks_project_milestone_id_foreign` FOREIGN KEY (`project_milestone_id`) REFERENCES `project_milestones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_assigned_teacher_foreign` FOREIGN KEY (`assigned_teacher`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `project_books`
--
ALTER TABLE `project_books`
  ADD CONSTRAINT `project_books_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_book_chapters`
--
ALTER TABLE `project_book_chapters`
  ADD CONSTRAINT `project_book_chapters_project_book_id_foreign` FOREIGN KEY (`project_book_id`) REFERENCES `project_books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_milestones`
--
ALTER TABLE `project_milestones`
  ADD CONSTRAINT `project_milestones_done_by_foreign` FOREIGN KEY (`done_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_milestones_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_user`
--
ALTER TABLE `project_user`
  ADD CONSTRAINT `project_user_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
