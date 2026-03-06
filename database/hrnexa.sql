-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 24, 2026 at 06:59 AM
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
-- Database: `hrnexa`
--

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `default_amount` decimal(12,2) DEFAULT NULL,
  `designation_level` varchar(50) DEFAULT 'All',
  `basic_salary` decimal(12,2) DEFAULT 0.00,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allowances`
--

INSERT INTO `allowances` (`id`, `name`, `type`, `amount_type`, `default_amount`, `designation_level`, `basic_salary`, `is_taxable`, `is_active`) VALUES
(1, 'House Rent Allowance (HRA)', 'monthly', 'percentage', 4000.00, 'Junior', 10000.00, 0, 1),
(2, 'Medical Allowance', 'monthly', 'percentage', 10.00, 'All', 10000.00, 0, 1),
(3, 'Mobile / Internet Allowance', 'monthly', 'percentage', 5.00, 'All', 10000.00, 0, 1),
(4, 'Basic Allowance', 'monthly', 'fixed', 2.00, 'All', 0.00, 1, 1),
(5, 'Dearness Allowance', 'monthly', 'percentage', 5.00, 'All', 0.00, 1, 1),
(6, 'Conveyance Allowance', 'monthly', 'fixed', 1500.00, 'All', 0.00, 0, 1),
(7, 'Medical Allowance', 'monthly', 'fixed', 5.00, 'All', 0.00, 0, 1),
(8, 'House Rent Allowance (HRA)', 'monthly', 'fixed', 3000.00, 'All', 0.00, 0, 1),
(9, 'Full Attendance Bonus', 'monthly', 'fixed', 500.00, 'All', 0.00, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `appraisal_cycles`
--

CREATE TABLE `appraisal_cycles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appraisal_cycles`
--

INSERT INTO `appraisal_cycles` (`id`, `name`, `start_date`, `end_date`, `status`, `created_by`, `created_at`) VALUES
(1, 'dfdsfdsfdsfd dfdf', '2026-02-10', '2026-03-31', 'draft', 2, '2026-02-06 10:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('available','assigned','maintenance','disposed') NOT NULL DEFAULT 'available',
  `location` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_code`, `name`, `category`, `brand`, `model`, `serial_number`, `purchase_date`, `warranty_expiry`, `status`, `location`, `created_at`) VALUES
(1, 'AST-2025-001', 'Laptop Dell XPS 15', 'Electronics', 'Dell', 'XPS 15 9530', 'SN-DELL-XPS-001', '2025-01-10', '2028-01-10', 'assigned', 'Dhaka Office', '2026-02-06 11:18:57'),
(2, 'AST-2025-002', 'Monitor LG 27UK850', 'Electronics', 'LG', '27UK850-W', 'SN-LG-MON-002', '2025-01-10', '2027-01-10', 'assigned', 'Dhaka Office', '2026-02-06 11:18:57'),
(3, 'AST-2025-003', 'Office Chair Ergonomic', 'Furniture', 'Herman Miller', 'Aeron', 'CH-HM-003', '2024-12-05', '2029-12-05', 'available', 'Storeroom', '2026-02-06 11:18:57'),
(4, 'AST-2025-004', 'MacBook Pro M3', 'Electronics', 'Apple', 'M3 Pro 14\"', 'SN-APPLE-MBP-004', '2025-02-01', '2026-02-01', 'available', 'IT Desk', '2026-02-06 11:18:57'),
(5, 'AST-2025-005', 'Laptop HP Pavilion', 'Electronics', 'HP', 'Pavilion 15', 'SN-HP-PAV-005', '2024-11-20', '2025-11-20', 'assigned', 'Remote', '2026-02-06 11:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

CREATE TABLE `asset_assignments` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `condition_at_assignment` text DEFAULT NULL,
  `condition_at_return` text DEFAULT NULL,
  `status` enum('assigned','returned','damaged','lost') NOT NULL DEFAULT 'assigned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_assignments`
--

INSERT INTO `asset_assignments` (`id`, `asset_id`, `employee_id`, `assigned_date`, `return_date`, `condition_at_assignment`, `condition_at_return`, `status`) VALUES
(1, 1, 2, '2026-01-07', NULL, 'Working perfectly', NULL, 'assigned'),
(2, 2, 5, '2026-01-22', NULL, 'Brand new', NULL, 'assigned'),
(3, 5, 2, '2025-12-08', NULL, 'Slight scratch on lid', NULL, 'assigned');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `punch_in` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `punch_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `break_time` int(11) NOT NULL DEFAULT 0,
  `total_hours` decimal(4,2) DEFAULT NULL,
  `overtime_hours` decimal(4,2) NOT NULL DEFAULT 0.00,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `punch_in`, `punch_out`, `break_time`, `total_hours`, `overtime_hours`, `status`, `approved_by`, `approved_at`) VALUES
(370, 7, '2026-02-23', '2026-02-23 02:19:00', '2026-02-23 13:20:00', 0, 11.02, 0.00, 'present', NULL, '0000-00-00 00:00:00'),
(371, 5, '2026-02-23', '2026-02-23 01:41:00', '2026-02-23 13:41:00', 0, 12.00, 0.00, 'present', NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_corrections`
--

CREATE TABLE `attendance_corrections` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `original_punch_in` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `original_punch_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `corrected_punch_in` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `corrected_punch_out` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `reason` text NOT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_corrections`
--

INSERT INTO `attendance_corrections` (`id`, `employee_id`, `date`, `original_punch_in`, `original_punch_out`, `corrected_punch_in`, `corrected_punch_out`, `reason`, `requested_by`, `approved_by`, `status`, `created_at`) VALUES
(25, 7, '2026-02-23', '2026-02-23 13:52:57', '0000-00-00 00:00:00', '2026-02-23 02:19:00', '2026-02-23 13:20:00', 'aa', NULL, 5, 'approved', '2026-02-23 13:19:40'),
(26, 5, '2026-02-23', '2026-02-23 13:52:55', '0000-00-00 00:00:00', '2026-02-23 01:41:00', '2026-02-23 13:41:00', 'sdsdfs', 9, NULL, 'approved', '2026-02-23 13:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `timestamp`) VALUES
(195, 9, 'Logout', 'users', 9, NULL, NULL, '2026-02-22 21:51:11'),
(196, 2, 'Login', 'users', 2, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-22 21:51:17'),
(197, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 06:25:29'),
(198, 1, 'Created Backup', 'system_backups', 4, NULL, '{\"filename\":\"backup_database_20260223_125753.sql\",\"type\":\"Database\"}', '2026-02-23 06:57:53'),
(199, 1, 'Logout', 'users', 1, NULL, NULL, '2026-02-23 12:57:35'),
(200, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 12:57:51'),
(201, 1, 'Logout', 'users', 1, NULL, NULL, '2026-02-23 13:08:20'),
(202, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:16:05'),
(203, 1, 'Logout', 'users', 1, NULL, NULL, '2026-02-23 13:16:08'),
(204, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:16:52'),
(205, 1, 'Disabled Module', '0', 1, NULL, '{\"message\":\"Disabled Module: Attendance Management\",\"module_id\":1,\"status\":\"inactive\"}', '2026-02-23 13:17:02'),
(206, 1, 'Enabled Module', '0', 1, NULL, '{\"message\":\"Enabled Module: Attendance Management\",\"module_id\":1,\"status\":\"active\"}', '2026-02-23 13:17:03'),
(207, 1, 'Logout', 'users', 1, NULL, NULL, '2026-02-23 13:17:31'),
(208, NULL, 'Login', 'employees', 7, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:17:39'),
(209, NULL, 'Logout', 'employees', 7, NULL, NULL, '2026-02-23 13:20:11'),
(210, 9, 'Login', 'users', 9, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:20:16'),
(211, 9, 'Logout', 'users', 9, NULL, NULL, '2026-02-23 13:44:53'),
(212, 3, 'Login', 'users', 3, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:45:00'),
(213, 3, 'Logout', 'users', 3, NULL, NULL, '2026-02-23 13:54:10'),
(214, 2, 'Login', 'users', 2, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:54:15'),
(215, 2, 'Logout', 'users', 2, NULL, NULL, '2026-02-23 13:54:51'),
(216, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 13:54:56'),
(217, 1, 'Generated Compliance Report', 'compliance_reports', 7, NULL, NULL, '2026-02-23 13:56:35'),
(218, 1, 'Logout', 'users', 1, NULL, NULL, '2026-02-23 15:38:01'),
(219, 1, 'Login', 'users', 1, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/145.0.0.0 Safari\\/537.36 Edg\\/145.0.0.0\"}', '2026-02-23 15:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Bangladeshi',
  `resume_path` varchar(255) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `status` enum('new','screening','interview','selected','rejected') NOT NULL DEFAULT 'new',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `father_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `home_district` varchar(100) DEFAULT NULL,
  `thana_upazila` varchar(100) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `nid_no` varchar(50) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `present_address` text DEFAULT NULL,
  `experience_level` enum('Fresher','Experienced') DEFAULT NULL,
  `last_company` varchar(255) DEFAULT NULL,
  `last_job_title` varchar(100) DEFAULT NULL,
  `experience_duration` varchar(100) DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `professional_certs` text DEFAULT NULL,
  `other_skills` text DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `expected_salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidate_applications`
--

CREATE TABLE `candidate_applications` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_requisition_id` int(11) NOT NULL,
  `status` enum('applied','screening','interview','selected','rejected') NOT NULL DEFAULT 'applied',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compliance_reports`
--

CREATE TABLE `compliance_reports` (
  `id` int(11) NOT NULL,
  `report_name` varchar(100) NOT NULL,
  `category` enum('Security','Access','Data Privacy','Operational') DEFAULT 'Security',
  `status` enum('Compliant','Non-Compliant','Warning') DEFAULT 'Compliant',
  `summary` text DEFAULT NULL,
  `findings_count` int(11) DEFAULT 0,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `compliance_reports`
--

INSERT INTO `compliance_reports` (`id`, `report_name`, `category`, `status`, `summary`, `findings_count`, `generated_by`, `generated_at`) VALUES
(4, 'System Audit - Feb 2026', 'Security', 'Warning', 'Automated compliance check performed on 2026-02-06 14:47:37. Audited system logs, user permissions, and database integrity. Found 1 minor configuration issues.', 1, 1, '2026-02-06 08:47:37'),
(5, 'System Audit - Feb 2026', 'Security', 'Warning', 'Automated compliance check performed on 2026-02-08 11:59:36. Audited system logs, user permissions, and database integrity. Found 2 minor configuration issues.', 2, 1, '2026-02-08 05:59:36'),
(6, 'System Audit - Feb 2026', 'Security', 'Compliant', 'Automated compliance check performed on 2026-02-19 00:48:36. Audited system logs, user permissions, and database integrity. No issues found.', 0, 1, '2026-02-18 18:48:36'),
(7, 'System Audit - Feb 2026', 'Security', 'Compliant', 'Automated compliance check performed on 2026-02-23 19:56:35. Audited system logs, user permissions, and database integrity. No issues found.', 0, 1, '2026-02-23 13:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deduction_code` varchar(20) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `amount_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `calculation_basis` enum('basic','gross','total_earnings') DEFAULT 'gross',
  `default_amount` decimal(12,2) DEFAULT NULL,
  `max_limit` decimal(10,2) DEFAULT NULL,
  `designation_level` varchar(50) DEFAULT NULL,
  `is_mandatory` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `category` enum('Statutory','Policy','Voluntary') NOT NULL DEFAULT 'Policy',
  `auto_calculation` enum('Yes','No') DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`id`, `name`, `deduction_code`, `type`, `amount_type`, `calculation_basis`, `default_amount`, `max_limit`, `designation_level`, `is_mandatory`, `is_active`, `category`, `auto_calculation`) VALUES
(15, 'Income Tax', 'DED-INC5993', 'monthly', 'fixed', 'basic', 250.00, NULL, 'Lead', 1, 1, 'Policy', 'Yes'),
(16, 'Absent', 'DED-ABS4064', 'monthly', 'fixed', 'basic', 300.00, NULL, 'All', 1, 1, 'Policy', 'Yes'),
(17, 'Loan Deduction', 'DED-LOA2320', 'monthly', 'fixed', 'basic', 15000.00, NULL, 'Junior', 0, 1, 'Policy', 'Yes'),
(18, 'Loan Deduction', 'DED-LOA9885', 'monthly', 'fixed', 'basic', 20000.00, NULL, 'Mid-Level', 1, 1, 'Policy', 'Yes'),
(19, 'Loan Deduction', 'DED-LOA1014', 'monthly', 'fixed', 'basic', 25000.00, NULL, 'Senior', 1, 1, 'Policy', 'Yes'),
(20, 'Provident Fund', NULL, 'monthly', 'percentage', 'gross', 12.00, NULL, NULL, 1, 1, 'Policy', 'Yes'),
(21, 'Income Tax', NULL, 'monthly', 'fixed', 'gross', 10.00, NULL, NULL, 1, 1, 'Policy', 'Yes'),
(22, 'Employee Insurance', NULL, 'monthly', 'fixed', 'gross', 500.00, NULL, NULL, 0, 1, 'Policy', 'Yes'),
(23, 'Loan Deduction', NULL, 'monthly', 'fixed', 'gross', 1000.00, NULL, NULL, 0, 1, 'Policy', 'Yes'),
(24, 'Professional Tax', NULL, 'monthly', 'fixed', 'gross', 200.00, NULL, NULL, 0, 1, 'Policy', 'Yes'),
(25, 'Absent Deduction', NULL, 'monthly', 'percentage', 'gross', 0.00, NULL, NULL, 0, 1, 'Policy', 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `head_id`, `parent_id`, `is_active`, `created_at`) VALUES
(27, 'Information Technology', 'Manages IT infrastructure, software development, and technical support', NULL, NULL, 1, '2026-01-25 16:52:55'),
(41, 'Development Department', 'The Development Department is the core driving force of an IT company. The main responsibility of this department is to design, develop, test, and maintain software, web applications, mobile applications, and internal systems according to the needs of clients or the company.', NULL, NULL, 1, '2026-02-04 20:53:45'),
(42, 'UI / UX Design Department', 'The UI / UX Design Department is responsible for creating visually appealing, user-friendly, and intuitive digital interfaces for software, websites, and mobile applications. This department focuses on enhancing the user experience (UX) by understanding user behavior, needs, and expectations, while designing clean and consistent user interfaces (UI).', NULL, NULL, 1, '2026-02-04 20:54:44'),
(43, 'Quality Assurance (QA) / Testing Department', 'The Quality Assurance (QA) / Testing Department is responsible for ensuring that software products meet the required quality standards before release. This department focuses on identifying bugs, errors, and performance issues to ensure that applications are stable, secure, and reliable.', NULL, NULL, 1, '2026-02-04 20:55:29'),
(44, 'IT Support / Infrastructure Department', 'The IT Support / Infrastructure Department is responsible for managing and maintaining the company’s IT infrastructure, systems, and technical support services. This department ensures that hardware, software, networks, and servers operate smoothly, securely, and efficiently to support daily business operations.', NULL, NULL, 1, '2026-02-04 20:56:19'),
(45, 'Research & Development (R&D)', 'The Research & Development (R&D) Department focuses on exploring new technologies, tools, and innovative solutions to improve existing products and develop new ones.', NULL, NULL, 1, '2026-02-04 20:56:58'),
(46, 'Human Resource (HR) Department', 'The Human Resource (HR) Department is responsible for managing the complete employee lifecycle within an organization.', NULL, NULL, 1, '2026-02-04 20:57:37'),
(47, 'Sales & Marketing Department', 'The Sales & Marketing Department is responsible for promoting the company’s products or services and generating revenue.', NULL, NULL, 1, '2026-02-04 20:58:18'),
(48, 'Client Service', 'The Client Service Department is responsible for maintaining strong relationships with clients by providing timely support and effective solutions to their issues.', NULL, NULL, 1, '2026-02-04 20:59:10'),
(49, 'Project Management Department', 'The Project Management Department is responsible for planning, executing, and closing projects efficiently while ensuring they meet the defined goals, deadlines, and budgets.', NULL, NULL, 1, '2026-02-04 21:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `name`, `description`, `level`, `department_id`, `is_active`, `created_at`) VALUES
(97, 'Junior Developer', '', 'Junior', 41, 1, '2026-02-06 09:26:32');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `team_leader_id` int(11) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `probation_start_date` date DEFAULT NULL,
  `employment_status` enum('active','probation','terminated','resigned') NOT NULL DEFAULT 'probation',
  `probation_end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `account_locked` tinyint(1) DEFAULT 0,
  `locked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `date_of_birth`, `gender`, `address`, `department_id`, `designation_id`, `team_leader_id`, `hire_date`, `probation_start_date`, `employment_status`, `probation_end_date`, `is_active`, `created_at`, `updated_at`, `login_attempts`, `account_locked`, `locked_at`) VALUES
(2, 'TL001', 'Team', 'Leader', 'teamleader@hrnexa.com', '\\.cXdwLetEeCoSbsUkAjqpSZt3DiunjSTLJGngcnoO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01', NULL, 'active', NULL, 1, '2026-01-25 18:09:42', '2026-01-25 18:09:42', 0, 0, NULL),
(5, 'TL202673497', 'Asad', 'Khan', 'asadkhan@gmail.com', '$2y$10$6d072u6hV3vWgSZhgc3QU.g9Zz1obwWU.URApWGzv5zFUTYl3vH9i', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 07:30:53', '2026-02-06 07:30:53', 0, 0, NULL),
(7, 'EMP202671257', 'Tamim', 'Iqbal', 'tamimiqbal@gmail.com', '$2y$10$c/rj8MPopZy3FWNXIpuvu.qBgGCn/aumx35/AF/.5uZcvS.3n1nuW', '01912345678', '1997-10-10', 'male', 'Dhaka', 41, 97, 5, '2026-02-01', NULL, 'active', NULL, 1, '2026-02-06 09:27:57', '2026-02-07 16:53:44', 0, 0, NULL),
(8, 'EMP001', 'Sakib', 'Islam', 'sakib@example.com', '$2y$10$fIz/dntt4b6N.lyf.ynUU.cm6Nt.d.KoY6ce76QLTpI5l.Vk/S.Rq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-14 11:21:23', 0, 0, NULL),
(9, 'EMP002', 'Mushfiqur', 'Rahim', 'mushfiq@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(10, 'EMP003', 'Mahmudullah', 'Riyad', 'riyad@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(11, 'EMP004', 'Mustafizur', 'Rahman', 'fizz@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'probation', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(12, 'EMP005', 'Litton', 'Das', 'litton@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'probation', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(13, 'EMP006', 'Taskin', 'Ahmed', 'taskin@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(14, 'EMP007', 'Shoriful', 'Islam', 'shoriful@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'probation', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(15, 'EMP008', 'Mehidy', 'Hasan', 'miraz@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(16, 'EMP009', 'Najmul', 'Shanto', 'shanto@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'active', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL),
(17, 'EMP010', 'Towhid', 'Hridoy', 'hridoy@example.com', 'hashed_pass', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-06', NULL, 'probation', NULL, 1, '2026-02-06 13:01:16', '2026-02-06 13:01:16', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_allowances`
--

CREATE TABLE `employee_allowances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `allowance_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_allowances`
--

INSERT INTO `employee_allowances` (`id`, `employee_id`, `allowance_id`, `amount`, `effective_from`, `effective_to`) VALUES
(1, 2, 4, 5000.00, '2026-01-01', NULL),
(2, 5, 4, 8000.00, '2026-01-01', NULL),
(3, 7, 4, 10000.00, '2026-01-01', NULL),
(4, 8, 4, 6000.00, '2026-01-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `deduction_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `exemption_flag` tinyint(1) DEFAULT 0,
  `special_note` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_deductions`
--

INSERT INTO `employee_deductions` (`id`, `employee_id`, `deduction_id`, `amount`, `effective_from`, `effective_to`, `is_active`, `exemption_flag`, `special_note`, `start_date`, `end_date`) VALUES
(4, 2, 15, 500.00, '2026-01-01', NULL, 1, 0, NULL, NULL, NULL),
(5, 5, 15, 1500.00, '2026-01-01', NULL, 1, 0, NULL, NULL, NULL),
(6, 7, 15, 2000.00, '2026-01-01', NULL, 1, 0, NULL, NULL, NULL),
(7, 8, 15, 800.00, '2026-01-01', NULL, 1, 0, NULL, NULL, NULL),
(8, 2, 25, 2043.97, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(9, 5, 25, 4651.10, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(10, 7, 25, 6778.27, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(11, 8, 25, 1516.50, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(12, 9, 25, 3623.60, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(13, 10, 25, 1709.60, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(14, 11, 25, 3052.33, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(15, 12, 25, 983.60, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(16, 13, 25, 1291.40, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(17, 15, 25, 4627.30, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(18, 16, 25, 3118.13, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL),
(19, 17, 25, 2985.27, '2026-02-01', NULL, 1, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_documents`
--

INSERT INTO `employee_documents` (`id`, `employee_id`, `document_type`, `file_path`, `expiry_date`, `verification_status`, `uploaded_at`) VALUES
(6, 5, 'Certificate', '../Upload/employee_document/pending/TL202673497-TL-20260206160534.pdf', NULL, 'verified', '2026-02-06 10:05:34'),
(7, 7, 'Other', '../Upload/employee_document/pending/EMP202671257-20260206160719.pdf', NULL, 'verified', '2026-02-06 10:07:19'),
(8, 5, 'Certificate', '../Upload/employee_document/pending/TL202673497-TL-20260223194142.pdf', NULL, 'pending', '2026-02-23 13:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `employee_emergency_contacts`
--

CREATE TABLE `employee_emergency_contacts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_emergency_contacts`
--

INSERT INTO `employee_emergency_contacts` (`id`, `employee_id`, `name`, `relationship`, `phone`, `email`, `address`) VALUES
(2, 2, 'ttttt', 'tttttttt', '12412454545', 'ttttt@gmail.com', 'tttttttttttlllll'),
(4, 5, 'Bani Amin', 'Bother', '01912345678', 'baniamin@gmail.com', 'Kazipara, Mirpur, Dhaka'),
(5, 7, 'Nafez Iqbal', 'Brother', '01912345678', 'nafez@gmail.com', 'Bogura');

-- --------------------------------------------------------

--
-- Table structure for table `employee_goals`
--

CREATE TABLE `employee_goals` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `kpi_id` int(11) NOT NULL,
  `target_value` decimal(10,2) NOT NULL,
  `achievement_value` decimal(10,2) DEFAULT NULL,
  `review_period` varchar(20) NOT NULL,
  `status` enum('assigned','in_progress','completed','cancelled') NOT NULL DEFAULT 'assigned',
  `assigned_by` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaries`
--

CREATE TABLE `employee_salaries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `salary_structure_id` int(11) NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_salaries`
--

INSERT INTO `employee_salaries` (`id`, `employee_id`, `salary_structure_id`, `basic_salary`, `effective_from`, `effective_to`) VALUES
(1, 2, 1, 61319.00, '2026-01-01', NULL),
(2, 5, 1, 46511.00, '2026-01-01', NULL),
(3, 7, 1, 50837.00, '2026-01-01', NULL),
(4, 8, 1, 45495.00, '2026-01-01', NULL),
(5, 9, 1, 36236.00, '2026-01-01', NULL),
(6, 10, 1, 25644.00, '2026-01-01', NULL),
(7, 11, 1, 45785.00, '2026-01-01', NULL),
(8, 12, 1, 29508.00, '2026-01-01', NULL),
(9, 13, 1, 38742.00, '2026-01-01', NULL),
(10, 14, 1, 54696.00, '2026-01-01', NULL),
(11, 15, 1, 46273.00, '2026-01-01', NULL),
(12, 16, 1, 46772.00, '2026-01-01', NULL),
(13, 17, 1, 44779.00, '2026-01-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_shifts`
--

CREATE TABLE `employee_shifts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_skills`
--

CREATE TABLE `employee_skills` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
  `certified_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_trainings`
--

CREATE TABLE `employee_trainings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `training_program_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `status` enum('enrolled','in_progress','completed','dropped') NOT NULL DEFAULT 'enrolled',
  `certificate_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_trainings`
--

INSERT INTO `employee_trainings` (`id`, `employee_id`, `training_program_id`, `enrollment_date`, `completion_date`, `status`, `certificate_path`) VALUES
(4, 2, 6, '2026-01-27', '2026-02-04', 'completed', NULL),
(5, 2, 5, '2026-02-06', NULL, 'enrolled', NULL),
(6, 5, 6, '2026-01-27', '2026-02-04', 'completed', NULL),
(7, 5, 7, '2026-02-04', NULL, 'in_progress', NULL),
(8, 7, 7, '2026-02-04', NULL, 'in_progress', NULL),
(9, 5, 8, '2026-02-23', NULL, 'enrolled', NULL),
(10, 16, 8, '2026-02-23', NULL, 'enrolled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `max_limit` decimal(12,2) DEFAULT NULL,
  `requires_receipt` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `description`, `max_limit`, `requires_receipt`, `is_active`) VALUES
(1, 'Travel', 'Business travel related expenses (Flight, Hotel, etc.)', 50000.00, 1, 1),
(2, 'Meal', 'Business lunch/dinner expenses', 5000.00, 1, 1),
(3, 'Office Supplies', 'Stationery, printing, etc.', 2000.00, 1, 1),
(4, 'Training', 'Professional development courses', 20000.00, 1, 1),
(5, 'Internet/Communication', 'Mobile and internet bill reimbursements', 3000.00, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `expense_claims`
--

CREATE TABLE `expense_claims` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `claim_date` date NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected','paid') NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_claims`
--

INSERT INTO `expense_claims` (`id`, `employee_id`, `category_id`, `amount`, `description`, `claim_date`, `receipt_path`, `status`, `submitted_at`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 2, 1, 12000.00, 'Regional visit to Chittagong', '2026-02-01', NULL, 'submitted', '2026-02-06 11:13:57', NULL, '0000-00-00 00:00:00', '2026-02-06 11:13:57'),
(2, 5, 2, 1500.50, 'Client lunch at Westin', '2026-02-04', NULL, 'approved', '2026-02-23 13:53:59', 3, '2026-02-23 13:53:59', '2026-02-06 11:13:57'),
(3, 2, 3, 500.00, 'New notebook and pens', '2026-01-27', NULL, 'approved', '2026-02-06 11:13:57', NULL, '0000-00-00 00:00:00', '2026-02-06 11:13:57'),
(4, 7, 4, 8000.00, 'Advanced Excel Training', '2026-02-05', NULL, 'approved', '2026-02-06 11:22:00', 3, '2026-02-06 11:22:00', '2026-02-06 11:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `type` enum('national','regional','company') NOT NULL DEFAULT 'company',
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  `applicable_locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_locations`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_audit_logs`
--

CREATE TABLE `hr_audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_requisition_id` int(11) NOT NULL,
  `interviewer_id` int(11) NOT NULL,
  `scheduled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `feedback` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 10),
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_requisitions`
--

CREATE TABLE `job_requisitions` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `department_id` int(11) NOT NULL,
  `positions` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `experience` varchar(255) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `expected_joining_date` date DEFAULT NULL,
  `hiring_reason` text DEFAULT NULL,
  `status` enum('draft','open','closed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_requisitions`
--

INSERT INTO `job_requisitions` (`id`, `title`, `department_id`, `positions`, `description`, `requirements`, `experience`, `salary_range`, `expected_joining_date`, `hiring_reason`, `status`, `created_by`, `created_at`) VALUES
(1, 'Software Engineer', 27, 3, 'Full-stack developer with React/Node experience.', '3+ years experience, Computer Science degree.', NULL, NULL, NULL, NULL, 'cancelled', 2, '2026-02-06 12:01:00'),
(2, 'HR Executive', 41, 1, 'Handle recruitment and employee relations.', 'Excellent communication skills, HR degree.', NULL, NULL, NULL, NULL, 'closed', 2, '2026-02-06 12:01:00'),
(3, 'Product Manager', 42, 2, 'Lead product development lifecycle.', 'Prior experience in agile environments.', NULL, NULL, NULL, NULL, 'closed', 2, '2026-02-06 12:01:00'),
(4, 'Devloper', 41, 5, '', 'PHP, Laravel', '1', '20', '2026-03-01', '', 'open', 5, '2026-02-22 15:47:41'),
(5, 'UI designer', 42, 5, 'dsfsdfsdf', 'dfdsfs', '1 year', '20k', '2026-03-01', 'dfsdfsdf', 'open', 5, '2026-02-23 13:40:46');

-- --------------------------------------------------------

--
-- Table structure for table `kpis`
--

CREATE TABLE `kpis` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `measurement_criteria` text DEFAULT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `weight` decimal(5,2) NOT NULL DEFAULT 1.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','referred') NOT NULL DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `employee_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `applied_at`, `approved_by`, `approved_at`) VALUES
(28, 7, 1, '2026-02-01', '2026-02-05', 5, 'dfd', 'approved', '2026-02-23 13:19:53', 5, '2026-02-23 13:42:29'),
(29, 5, 2, '2026-02-02', '2026-02-05', 4, 'dsfsdf', 'approved', '2026-02-23 13:42:21', NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `leave_policies`
--

CREATE TABLE `leave_policies` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `allocated_days` int(11) NOT NULL,
  `used_days` int(11) NOT NULL DEFAULT 0,
  `balance_days` int(11) NOT NULL,
  `year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `max_days_per_year` int(11) NOT NULL DEFAULT 0,
  `carry_forward_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `encashment_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `description`, `max_days_per_year`, `carry_forward_allowed`, `encashment_allowed`, `is_active`) VALUES
(1, 'Annual Leave', 'Yearly vacation leave', 10, 1, 0, 1),
(2, 'Sick Leave', 'Medical leave', 14, 0, 0, 1),
(3, 'Maternity Leave', 'Maternity leave for female employees', 120, 0, 0, 1),
(4, 'Paternity Leave', 'Paternity leave for male employees', 15, 0, 0, 1),
(5, 'Emergency Leave', 'Emergency situations', 5, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `level_allowances`
--

CREATE TABLE `level_allowances` (
  `id` int(11) NOT NULL,
  `level` varchar(50) NOT NULL,
  `allowance_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_name`, `display_name`, `description`, `icon`, `status`, `created_at`, `updated_at`) VALUES
(1, 'attendance_management', 'Attendance Management', 'Track employee clock-in/out and shifts', 'fa-calendar-check', 'active', '2026-02-04 10:24:58', '2026-02-23 13:17:03'),
(2, 'leave_management', 'Leave Management', 'Manage employee leave requests and balances', 'fa-paper-plane', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(3, 'payroll_management', 'Payroll & Payslips', 'Generate salary and handle payslips', 'fa-money-bill-wave', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(4, 'recruitment_system', 'Recruitment System', 'Manage job postings and candidates', 'fa-user-plus', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(5, 'performance_appraisal', 'Performance Appraisals', 'Track employee performance and reviews', 'fa-chart-line', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(6, 'document_management', 'Document Management', 'Secure storage for employee documents', 'fa-file-alt', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(7, 'training_development', 'Training & Development', 'Manage employee programs and progress', 'fa-graduation-cap', 'active', '2026-02-04 10:24:58', '2026-02-04 10:24:58'),
(8, 'expense_claims', 'Expense Claims', 'Process employee business expense requests', 'fa-receipt', 'active', '2026-02-04 10:24:58', '2026-02-06 00:18:14');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` enum('important','update','holiday') DEFAULT 'update',
  `footer_label` varchar(50) DEFAULT NULL,
  `footer_value` varchar(100) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `content`, `category`, `footer_label`, `footer_value`, `button_text`, `button_link`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Maintenance Schedule', 'The HRnexa platform will undergo scheduled maintenance to improve performance and security. During this time, the system will be temporarily unavailable.', 'important', 'Effective', 'Feb 22, 2026 (2 AM - 4 AM)', 'Read More', '#', 'active', '2026-02-22 19:29:09', '2026-02-22 19:29:09'),
(2, 'New Policy: Remote Work Guidelines', 'We have updated our remote work policies to offer more flexibility to our employees. Please review the updated employee handbook in the document portal.', 'update', 'By', 'HR Department', 'View Handbook', '#', 'active', '2026-02-22 19:29:09', '2026-02-22 19:29:09'),
(3, 'Upcoming Eid-ul-Fitr Holidays', 'Office will remain closed for 5 days due to Eid. We wish everyone a blessed and happy celebration with their families.', 'holiday', 'Closed', 'Apr 10 - Apr 15', 'Download PDF', '#', 'active', '2026-02-22 19:29:09', '2026-02-22 19:29:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offboarding`
--

CREATE TABLE `offboarding` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `resignation_date` date NOT NULL,
  `last_working_day` date DEFAULT NULL,
  `exit_reason` text DEFAULT NULL,
  `clearance_checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`clearance_checklist`)),
  `exit_interview_notes` text DEFAULT NULL,
  `settlement_status` enum('pending','processed','paid') NOT NULL DEFAULT 'pending',
  `status` enum('in_progress','completed') NOT NULL DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offboarding`
--

INSERT INTO `offboarding` (`id`, `employee_id`, `resignation_date`, `last_working_day`, `exit_reason`, `clearance_checklist`, `exit_interview_notes`, `settlement_status`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-01-27', '2026-02-26', 'Relocating to another city.', '[{\"item\":\"IT Assets Return\",\"status\":\"pending\"},{\"item\":\"Library Return\",\"status\":\"completed\"}]', NULL, 'pending', 'in_progress', '2026-02-06 12:30:46', '2026-02-06 12:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `offer_letters`
--

CREATE TABLE `offer_letters` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_requisition_id` int(11) NOT NULL,
  `salary_offered` decimal(12,2) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `status` enum('draft','sent','accepted','rejected','withdrawn') NOT NULL DEFAULT 'draft',
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `onboarding`
--

CREATE TABLE `onboarding` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `checklist_tasks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checklist_tasks`)),
  `asset_assignment_status` enum('pending','partially_assigned','completed') NOT NULL DEFAULT 'pending',
  `orientation_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onboarding`
--

INSERT INTO `onboarding` (`id`, `employee_id`, `checklist_tasks`, `asset_assignment_status`, `orientation_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, '[{\"task\":\"IT Setup\",\"status\":\"completed\"},{\"task\":\"HR Orientation\",\"status\":\"completed\"},{\"task\":\"ID Card Issue\",\"status\":\"pending\"}]', 'partially_assigned', '2026-02-08', 'in_progress', '2026-02-06 12:30:46', '2026-02-06 12:30:46'),
(2, 5, '[{\"task\":\"IT Setup\",\"status\":\"pending\"},{\"task\":\"HR Orientation\",\"status\":\"pending\"}]', 'partially_assigned', '2026-02-11', 'in_progress', '2026-02-06 12:30:46', '2026-02-08 06:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(3, 'asadkhan@gmail.com', '2aaa21fce11a0998b5c6d6d2fece6bf02fbddf984c28e82ba56d9a107d1dcbc7', '2026-02-14 18:13:40', '2026-02-14 11:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `id` int(11) NOT NULL,
  `month` int(11) NOT NULL CHECK (`month` >= 1 and `month` <= 12),
  `year` year(4) NOT NULL,
  `status` enum('draft','processing','completed','cancelled','pending_approval') NOT NULL DEFAULT 'draft',
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approval_status` enum('Draft','Reviewed','Approved') DEFAULT 'Draft',
  `is_locked` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  `locked_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`id`, `month`, `year`, `status`, `processed_by`, `processed_at`, `created_at`, `approval_status`, `is_locked`, `approved_by`, `locked_by`) VALUES
(6, 2, '2026', 'completed', NULL, '2026-02-14 16:41:33', '2026-02-14 15:17:39', 'Draft', 0, NULL, NULL),
(7, 3, '2026', 'draft', NULL, '2026-02-23 13:53:26', '2026-02-23 13:53:21', 'Draft', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `total_allowances` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gross_salary` decimal(12,2) NOT NULL,
  `net_salary` decimal(12,2) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_days` int(11) DEFAULT 0,
  `total_leave` int(11) DEFAULT 0,
  `total_absent` int(11) DEFAULT 0,
  `total_late` int(11) DEFAULT 0,
  `half_days` int(11) DEFAULT 0,
  `status` enum('draft','pending_approval','approved','rejected') DEFAULT 'draft',
  `breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`breakdown`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `payroll_run_id`, `employee_id`, `basic_salary`, `total_allowances`, `total_deductions`, `gross_salary`, `net_salary`, `generated_at`, `start_date`, `end_date`, `total_days`, `total_leave`, `total_absent`, `total_late`, `half_days`, `status`, `breakdown`) VALUES
(57, 6, 2, 61319.00, 22268.80, 4858.78, 83587.80, 78729.02, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 0, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":\"5\",\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":6131.9},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":3065.95},{\"name\":\"Basic Allowance\",\"amount\":5000},{\"name\":\"Dearness Allowance\",\"amount\":3065.95},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":500},{\"name\":\"Income Tax\",\"amount\":4358.78}],\"summary\":{\"basic\":61319,\"total_allowances\":22268.8,\"total_deductions\":4858.78,\"tax\":4358.78,\"gross\":83587.8,\"net\":78729.02}}'),
(58, 6, 5, 46511.00, 22307.20, 5876.82, 67157.09, 62941.38, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 1, 4, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":\"11\",\"total_late\":4,\"late_penalty_absent\":1,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":1,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4651.1},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2325.55},{\"name\":\"Basic Allowance\",\"amount\":8000},{\"name\":\"Dearness Allowance\",\"amount\":2325.55},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":1661.11},{\"name\":\"Income Tax\",\"amount\":1500},{\"name\":\"Income Tax\",\"amount\":2715.71}],\"summary\":{\"basic\":46511,\"total_allowances\":22307.2,\"total_deductions\":5876.82,\"tax\":2715.71,\"gross\":67157.09,\"net\":62941.38}}'),
(59, 6, 7, 50837.00, 2058652.40, 310966.68, 2107673.79, 1798522.72, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 1, 4, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":\"13\",\"total_late\":4,\"late_penalty_absent\":1,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":1,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"House Rent Allowance (HRA)\",\"amount\":2033480},{\"name\":\"Medical Allowance\",\"amount\":5083.7},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2541.85},{\"name\":\"Basic Allowance\",\"amount\":10000},{\"name\":\"Dearness Allowance\",\"amount\":2541.85},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":1815.61},{\"name\":\"Income Tax\",\"amount\":2000},{\"name\":\"Income Tax\",\"amount\":307151.07}],\"summary\":{\"basic\":50837,\"total_allowances\":2058652.4,\"total_deductions\":310966.68,\"tax\":307151.07,\"gross\":2107673.79,\"net\":1798522.72}}'),
(60, 6, 8, 45495.00, 20104.00, 3359.90, 65599.00, 62239.10, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 2, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":\"2\",\"total_late\":2,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4549.5},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2274.75},{\"name\":\"Basic Allowance\",\"amount\":6000},{\"name\":\"Dearness Allowance\",\"amount\":2274.75},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":800},{\"name\":\"Income Tax\",\"amount\":2559.9}],\"summary\":{\"basic\":45495,\"total_allowances\":20104,\"total_deductions\":3359.9,\"tax\":2559.9,\"gross\":65599,\"net\":62239.1}}'),
(61, 6, 9, 36236.00, 12254.20, 2153.94, 47196.06, 46336.26, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 1, 3, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":3,\"late_penalty_absent\":1,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":1,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":3623.6},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1811.8},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1811.8},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":1294.14},{\"name\":\"Income Tax\",\"amount\":859.8}],\"summary\":{\"basic\":36236,\"total_allowances\":12254.2,\"total_deductions\":2153.94,\"tax\":859.8,\"gross\":47196.06,\"net\":46336.26}}'),
(62, 6, 10, 25644.00, 10135.80, 288.99, 35779.80, 35490.81, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 1, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":\"1\",\"total_late\":1,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":2564.4},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1282.2},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1282.2},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":288.99}],\"summary\":{\"basic\":25644,\"total_allowances\":10135.8,\"total_deductions\":288.99,\"tax\":288.99,\"gross\":35779.8,\"net\":35490.81}}'),
(63, 6, 11, 45785.00, 14164.00, 1994.90, 59949.00, 57954.10, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 0, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4578.5},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2289.25},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2289.25},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":1994.9}],\"summary\":{\"basic\":45785,\"total_allowances\":14164,\"total_deductions\":1994.9,\"tax\":1994.9,\"gross\":59949,\"net\":57954.1}}'),
(64, 6, 12, 29508.00, 10908.60, 520.83, 40416.60, 39895.77, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 1, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":1,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":2950.8},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1475.4},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1475.4},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":520.83}],\"summary\":{\"basic\":29508,\"total_allowances\":10908.6,\"total_deductions\":520.83,\"tax\":520.83,\"gross\":40416.6,\"net\":39895.77}}'),
(65, 6, 13, 38742.00, 12755.40, 1149.74, 51497.40, 50347.66, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 2, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":2,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":3874.2},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1937.1},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1937.1},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":1149.74}],\"summary\":{\"basic\":38742,\"total_allowances\":12755.4,\"total_deductions\":1149.74,\"tax\":1149.74,\"gross\":51497.4,\"net\":50347.66}}'),
(66, 6, 14, 54696.00, 15946.20, 3064.22, 70642.20, 67577.98, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 2, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":2,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":5469.6},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2734.8},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2734.8},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":3064.22}],\"summary\":{\"basic\":54696,\"total_allowances\":15946.2,\"total_deductions\":3064.22,\"tax\":3064.22,\"gross\":70642.2,\"net\":67577.98}}'),
(67, 6, 15, 46273.00, 14261.60, 3540.81, 58881.99, 56993.79, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 1, 4, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":4,\"late_penalty_absent\":1,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":1,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4627.3},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2313.65},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2313.65},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":1652.61},{\"name\":\"Income Tax\",\"amount\":1888.2}],\"summary\":{\"basic\":46273,\"total_allowances\":14261.6,\"total_deductions\":3540.81,\"tax\":1888.2,\"gross\":58881.99,\"net\":56993.79}}'),
(68, 6, 16, 46772.00, 14361.40, 2113.34, 61133.40, 59020.06, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 1, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":1,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4677.2},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2338.6},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2338.6},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":2113.34}],\"summary\":{\"basic\":46772,\"total_allowances\":14361.4,\"total_deductions\":2113.34,\"tax\":2113.34,\"gross\":61133.4,\"net\":59020.06}}'),
(69, 6, 17, 44779.00, 13962.80, 1874.18, 58741.80, 56867.62, '2026-02-14 15:17:42', NULL, NULL, 0, 0, 0, 2, 0, 'approved', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":2,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4477.9},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2238.95},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2238.95},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":1874.18}],\"summary\":{\"basic\":44779,\"total_allowances\":13962.8,\"total_deductions\":1874.18,\"tax\":1874.18,\"gross\":58741.8,\"net\":56867.62}}'),
(70, 7, 2, 61319.00, 22268.80, 1258200.74, 83587.80, -1174612.94, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":6131.9},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":3065.95},{\"name\":\"Basic Allowance\",\"amount\":5000},{\"name\":\"Dearness Allowance\",\"amount\":3065.95},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":500},{\"name\":\"Absent Deduction\",\"amount\":1253341.96},{\"name\":\"Income Tax\",\"amount\":4358.78}],\"summary\":{\"basic\":61319,\"total_allowances\":22268.8,\"total_deductions\":1258200.74,\"tax\":4358.78,\"gross\":83587.8,\"net\":-1174612.94}}'),
(71, 7, 5, 46511.00, 22307.20, 2167654.94, 68818.20, -2098836.74, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4651.1},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2325.55},{\"name\":\"Basic Allowance\",\"amount\":8000},{\"name\":\"Dearness Allowance\",\"amount\":2325.55},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":1500},{\"name\":\"Absent Deduction\",\"amount\":2163273.12},{\"name\":\"Income Tax\",\"amount\":2881.82}],\"summary\":{\"basic\":46511,\"total_allowances\":22307.2,\"total_deductions\":2167654.94,\"tax\":2881.82,\"gross\":68818.2,\"net\":-2098836.74}}'),
(72, 7, 7, 50837.00, 2058652.40, 3755292.53, 2109489.40, -1645803.13, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"House Rent Allowance (HRA)\",\"amount\":2033480},{\"name\":\"Medical Allowance\",\"amount\":5083.7},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2541.85},{\"name\":\"Basic Allowance\",\"amount\":10000},{\"name\":\"Dearness Allowance\",\"amount\":2541.85},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":2000},{\"name\":\"Absent Deduction\",\"amount\":3445869.12},{\"name\":\"Income Tax\",\"amount\":307423.41}],\"summary\":{\"basic\":50837,\"total_allowances\":2058652.4,\"total_deductions\":3755292.53,\"tax\":307423.41,\"gross\":2109489.4,\"net\":-1645803.13}}'),
(73, 7, 8, 45495.00, 20104.00, 693291.58, 65599.00, -627692.58, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4549.5},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2274.75},{\"name\":\"Basic Allowance\",\"amount\":6000},{\"name\":\"Dearness Allowance\",\"amount\":2274.75},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":800},{\"name\":\"Absent Deduction\",\"amount\":689931.68},{\"name\":\"Income Tax\",\"amount\":2559.9}],\"summary\":{\"basic\":45495,\"total_allowances\":20104,\"total_deductions\":693291.58,\"tax\":2559.9,\"gross\":65599,\"net\":-627692.58}}'),
(74, 7, 9, 36236.00, 12254.20, 1313972.21, 48490.20, -1265482.01, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":3623.6},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1811.8},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1811.8},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":1313047.7},{\"name\":\"Income Tax\",\"amount\":924.51}],\"summary\":{\"basic\":36236,\"total_allowances\":12254.2,\"total_deductions\":1313972.21,\"tax\":924.51,\"gross\":48490.2,\"net\":-1265482.01}}'),
(75, 7, 10, 25644.00, 10135.80, 438698.81, 35779.80, -402919.01, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":2564.4},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1282.2},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1282.2},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":438409.82},{\"name\":\"Income Tax\",\"amount\":288.99}],\"summary\":{\"basic\":25644,\"total_allowances\":10135.8,\"total_deductions\":438698.81,\"tax\":288.99,\"gross\":35779.8,\"net\":-402919.01}}'),
(76, 7, 11, 45785.00, 14164.00, 1399504.19, 59949.00, -1339555.19, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4578.5},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2289.25},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2289.25},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":1397509.29},{\"name\":\"Income Tax\",\"amount\":1994.9}],\"summary\":{\"basic\":45785,\"total_allowances\":14164,\"total_deductions\":1399504.19,\"tax\":1994.9,\"gross\":59949,\"net\":-1339555.19}}'),
(77, 7, 12, 29508.00, 10908.60, 290761.52, 40416.60, -250344.92, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":2950.8},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1475.4},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1475.4},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":290240.69},{\"name\":\"Income Tax\",\"amount\":520.83}],\"summary\":{\"basic\":29508,\"total_allowances\":10908.6,\"total_deductions\":290761.52,\"tax\":520.83,\"gross\":40416.6,\"net\":-250344.92}}'),
(78, 7, 13, 38742.00, 12755.40, 501463.93, 51497.40, -449966.53, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":3874.2},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":1937.1},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":1937.1},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":500314.19},{\"name\":\"Income Tax\",\"amount\":1149.74}],\"summary\":{\"basic\":38742,\"total_allowances\":12755.4,\"total_deductions\":501463.93,\"tax\":1149.74,\"gross\":51497.4,\"net\":-449966.53}}'),
(79, 7, 14, 54696.00, 15946.20, 3064.22, 70642.20, 67577.98, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":5469.6},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2734.8},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2734.8},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Income Tax\",\"amount\":3064.22}],\"summary\":{\"basic\":54696,\"total_allowances\":15946.2,\"total_deductions\":3064.22,\"tax\":3064.22,\"gross\":70642.2,\"net\":67577.98}}'),
(80, 7, 15, 46273.00, 14261.60, 2143243.99, 60534.60, -2082709.39, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4627.3},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2313.65},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2313.65},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":2141190.53},{\"name\":\"Income Tax\",\"amount\":2053.46}],\"summary\":{\"basic\":46273,\"total_allowances\":14261.6,\"total_deductions\":2143243.99,\"tax\":2053.46,\"gross\":60534.6,\"net\":-2082709.39}}'),
(81, 7, 16, 46772.00, 14361.40, 1460525.10, 61133.40, -1399391.70, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4677.2},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2338.6},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2338.6},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":1458411.76},{\"name\":\"Income Tax\",\"amount\":2113.34}],\"summary\":{\"basic\":46772,\"total_allowances\":14361.4,\"total_deductions\":1460525.1,\"tax\":2113.34,\"gross\":61133.4,\"net\":-1399391.7}}'),
(82, 7, 17, 44779.00, 13962.80, 1338648.23, 58741.80, -1279906.43, '2026-02-23 13:53:26', NULL, NULL, 0, 0, 0, 0, 0, 'draft', '{\"attendance_summary\":{\"total_leave\":0,\"approved_leave\":0,\"total_late\":0,\"late_penalty_absent\":0,\"total_absent_marked\":0,\"unapproved_leave_absent\":0,\"effective_absent_total\":0,\"half_days\":0,\"ot_hours\":0},\"allowances\":[{\"name\":\"Medical Allowance\",\"amount\":4477.9},{\"name\":\"Mobile \\/ Internet Allowance\",\"amount\":2238.95},{\"name\":\"Basic Allowance\",\"amount\":2},{\"name\":\"Dearness Allowance\",\"amount\":2238.95},{\"name\":\"Conveyance Allowance\",\"amount\":1500},{\"name\":\"Medical Allowance\",\"amount\":5},{\"name\":\"House Rent Allowance (HRA)\",\"amount\":3000},{\"name\":\"Full Attendance Bonus\",\"amount\":500}],\"deductions\":[{\"name\":\"Attendance Deductions\",\"amount\":0},{\"name\":\"Absent Deduction\",\"amount\":1336774.05},{\"name\":\"Income Tax\",\"amount\":1874.18}],\"summary\":{\"basic\":44779,\"total_allowances\":13962.8,\"total_deductions\":1338648.23,\"tax\":1874.18,\"gross\":58741.8,\"net\":-1279906.43}}');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `appraisal_cycle_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `self_rating` decimal(3,2) DEFAULT NULL,
  `manager_rating` decimal(3,2) DEFAULT NULL,
  `final_rating` decimal(3,2) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('pending','self_completed','manager_completed','finalized') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_reviews`
--

INSERT INTO `performance_reviews` (`id`, `employee_id`, `appraisal_cycle_id`, `reviewer_id`, `self_rating`, `manager_rating`, `final_rating`, `comments`, `status`, `created_at`) VALUES
(2, 2, 1, 5, 4.00, 4.20, 4.10, 'Excellent technical skills and teamwork.', 'finalized', '2026-02-06 11:48:36'),
(3, 7, 1, 5, 3.50, 3.80, NULL, 'Good progress this year.', 'manager_completed', '2026-02-06 11:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `action`, `description`) VALUES
(1, 'dashboard', 'view', 'View dashboard statistics'),
(2, 'employees', 'view', 'View employee list'),
(3, 'employees', 'create', 'Add new employees'),
(4, 'employees', 'edit', 'Edit employee details'),
(5, 'employees', 'delete', 'Delete employee records'),
(6, 'attendance', 'view', 'View attendance logs'),
(7, 'attendance', 'mark', 'Mark attendance'),
(8, 'attendance', 'approve', 'Approve attendance corrections'),
(9, 'leave', 'view', 'View leave applications'),
(10, 'leave', 'apply', 'Apply for leave'),
(11, 'leave', 'approve', 'Approve/Reject leave applications'),
(12, 'payroll', 'view', 'View payroll data'),
(13, 'payroll', 'generate', 'Generate monthly payroll'),
(14, 'payroll', 'approve', 'Approve payroll'),
(15, 'users', 'view', 'View system users'),
(16, 'users', 'create', 'Create system users'),
(17, 'users', 'edit', 'Edit system users'),
(18, 'users', 'delete', 'Delete system users'),
(19, 'system', 'settings', 'Manage system settings'),
(20, 'system', 'modules', 'Control system modules'),
(21, 'system', 'roles', 'Manage roles and permissions'),
(22, 'system', 'audit', 'View system audit logs'),
(23, 'system', 'backup', 'Manage system backups');

-- --------------------------------------------------------

--
-- Table structure for table `reporting_hierarchy`
--

CREATE TABLE `reporting_hierarchy` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `permissions`, `is_active`, `created_at`) VALUES
(1, 'Super Admin', 'System administrator with full access', '[\"attendance.approve\",\"attendance.mark\",\"attendance.view\",\"dashboard.view\",\"employees.create\",\"employees.delete\",\"employees.edit\",\"employees.view\",\"leave.apply\",\"leave.approve\",\"leave.view\",\"payroll.approve\",\"payroll.generate\",\"payroll.view\",\"system.audit\",\"system.backup\",\"system.modules\",\"system.roles\",\"system.settings\",\"users.create\",\"users.delete\",\"users.edit\",\"users.view\"]', 1, '2026-01-25 15:37:01'),
(2, 'Admin', 'Company administrator', '[\"attendance.view\",\"dashboard.view\",\"employees.create\",\"employees.delete\",\"employees.edit\",\"employees.view\",\"payroll.approve\",\"payroll.view\",\"users.create\",\"users.delete\",\"users.edit\",\"users.view\"]', 1, '2026-01-25 15:37:01'),
(3, 'HR', 'HR operations manager', '[\"employee.*\", \"recruitment.*\", \"leave.*\", \"attendance.*\"]', 1, '2026-01-25 15:37:01'),
(4, 'Team Leader', 'Team management and approvals', '[\"team.*\", \"approval.*\"]', 1, '2026-01-25 15:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_structures`
--

CREATE TABLE `salary_structures` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `allowances` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowances`)),
  `deductions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`deductions`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_structures`
--

INSERT INTO `salary_structures` (`id`, `name`, `basic_salary`, `allowances`, `deductions`, `is_active`, `created_at`) VALUES
(1, 'Junior level Salary Structure  ', 10000.00, NULL, NULL, 1, '2026-02-08 07:53:29');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_duration` int(11) NOT NULL DEFAULT 0,
  `is_night_shift` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `name`, `start_time`, `end_time`, `break_duration`, `is_night_shift`, `is_active`) VALUES
(1, 'Day Shift', '09:00:00', '17:00:00', 60, 0, 1),
(2, 'Evening Shift', '17:00:00', '01:00:00', 60, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_backups`
--

CREATE TABLE `system_backups` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `type` enum('Database','Files','Full') DEFAULT 'Database',
  `status` enum('Success','Failed') DEFAULT 'Success',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_backups`
--

INSERT INTO `system_backups` (`id`, `filename`, `file_size`, `type`, `status`, `created_by`, `created_at`) VALUES
(4, 'backup_database_20260223_125753.sql', '3 MB', 'Database', 'Success', 1, '2026-02-23 06:57:53');

-- --------------------------------------------------------

--
-- Table structure for table `system_holidays`
--

CREATE TABLE `system_holidays` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `holiday_date` date NOT NULL,
  `type` enum('National','Festival','Company','Other') DEFAULT 'National',
  `duration` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_holidays`
--

INSERT INTO `system_holidays` (`id`, `title`, `holiday_date`, `type`, `duration`, `description`, `created_at`) VALUES
(10, 'aaaa', '2026-02-01', 'National', 1, '', '2026-02-06 11:03:22'),
(11, 'bbbbbbb', '2026-02-03', 'Festival', 1, '', '2026-02-06 11:03:34'),
(12, 'cccc', '2026-02-05', 'Company', 1, '', '2026-02-06 11:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `system_policies`
--

CREATE TABLE `system_policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('General','Compliance','HR','Leave','Attendance','Payroll','Company') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_policies`
--

INSERT INTO `system_policies` (`id`, `title`, `description`, `category`, `is_active`, `created_at`) VALUES
(1, 'Code of Conduct', 'General guidelines for professional behavior and ethics.', 'General', 1, '2026-02-04 19:20:17'),
(2, 'Anti-Harassment Policy', 'Commitment to a workplace free from harassment and discrimination.', 'Compliance', 1, '2026-02-04 19:20:17'),
(3, 'Recruitment Policy', 'Standard procedures for hiring and onboarding new talent.', 'HR', 1, '2026-02-04 19:20:17'),
(4, 'Casual Leave Policy', 'Entitlement of 12 days per year for personal matters.', 'Leave', 1, '2026-02-04 19:20:17'),
(5, 'Working Hours Policy', 'Standard office timing: 09:00 AM to 06:00 PM.', 'Attendance', 1, '2026-02-04 19:20:17'),
(6, 'Salary Structure', 'Definitions of Basic Salary, HRA, and other components.', 'Payroll', 1, '2026-02-04 19:20:17'),
(7, 'Sick leave', 'qqqewewq c ewe', 'Leave', 0, '2026-02-06 10:28:16'),
(8, 'sdsdsads', 'sdsadsa', 'Company', 0, '2026-02-06 10:28:30'),
(9, 'sdsadsd', 'dfdfdf', 'Leave', 0, '2026-02-06 10:28:45'),
(10, 'sdsdsadsads', 'dsdsd', 'Company', 1, '2026-02-06 10:49:08'),
(11, 'dfdsfsdfd', 'fdsfsdfs', 'HR', 1, '2026-02-06 10:49:19'),
(12, 'dsfsdfsdfsdf', 'dfsdfsdfsd', 'Leave', 1, '2026-02-06 10:49:32'),
(13, 'dfdsfdsf', 'dsfdfdsfd', 'Company', 1, '2026-02-06 10:49:42'),
(14, 'dfdsfds', 'dfsdfsdf', 'Attendance', 1, '2026-02-06 10:49:52'),
(15, 'dfsdfsdf', 'dfdsfdsf', 'Payroll', 1, '2026-02-06 10:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `description`, `category`) VALUES
(1, 'company_name', 'HRnexa', 'The official name of the company', 'company'),
(2, 'company_reg_no', 'REG-2020-001234', 'Company registration number', 'company'),
(3, 'company_email', 'info@techsolutions.com', 'Official contact email', 'company'),
(4, 'company_phone', '+8801772353298', 'Official contact phone number', 'company'),
(5, 'company_address', 'Kazipara, Mirpur, Dhaka', 'Official company address', 'company'),
(6, 'weekly_off_days', '[\"Friday\"]', NULL, 'attendance');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `designation`, `image_path`, `linkedin`, `twitter`, `instagram`, `email`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(6, 'Asad khan', 'CEO & Founder', 'Upload/team/1771787003_699b52fb07fb9.jpg', '#', '#', '#', 'asadkhan409684@gmail.com', 'active', 1, '2026-02-22 18:52:52', '2026-02-22 19:03:23'),
(7, 'Al Ameen', 'Head of Engineering', 'Upload/team/1771787213_699b53cd5fb19.jpeg', '#', '#', '#', 'alameen@gmail.com', 'active', 2, '2026-02-22 18:53:29', '2026-02-22 19:06:53'),
(8, 'Abdulla Al Mosabbir', 'Admin', 'Upload/team/1771829130_699bf78abf816.png', '#', '#', '#', 'mosabbir@gmail.com', 'active', 3, '2026-02-22 18:54:29', '2026-02-23 06:45:30');

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `trainer` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planned','active','completed','cancelled') NOT NULL DEFAULT 'planned',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_programs`
--

INSERT INTO `training_programs` (`id`, `name`, `description`, `duration`, `trainer`, `capacity`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(5, 'Leadership Excellence', 'Development program for aspiring managers', 14, 'John Maxwell', 20, '2026-02-13', '2026-02-27', 'planned', '2026-02-06 11:26:01'),
(6, 'Advanced Web Security', 'Protecting applications against modern threats', 3, 'Security Experts Inc.', 15, '2026-02-01', '2026-02-04', 'completed', '2026-02-06 11:26:01'),
(7, 'Efficient Project Management', 'Mastering Agile and Scrum methodologies', 5, 'Sarah Jenkins', 25, '2026-02-05', '2026-02-10', 'active', '2026-02-06 11:26:01'),
(8, 'Communication Skills 101', 'Improving workplace communication and collaboration', 2, 'Emily Watson', 50, '2026-03-08', '2026-03-10', 'planned', '2026-02-06 11:26:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive','locked') NOT NULL DEFAULT 'active',
  `login_attempts` int(11) NOT NULL DEFAULT 0,
  `account_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`, `status`, `login_attempts`, `account_locked`, `locked_at`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'superadmin@hrnexa.com', '$2y$10$Ah5VFNQ2QK0zWeNeRD2ar.vy5Ipb4PbdoK5dFeiel6iC4UDqWAqra', 1, 'active', 0, 0, NULL, '2026-01-25 15:44:45', '2026-01-25 15:46:27'),
(2, 'admin', 'admin@hrnexa.com', '$2y$10$t62qSWdmR52cUbWQKBsU7ueBLHJLO5B75wpubyeMvbs4URxAcOjBC', 2, 'active', 0, 0, NULL, '2026-01-25 15:44:45', '2026-02-01 16:46:18'),
(3, 'hr', 'hr@hrnexa.com', '$2y$10$r7DNjZw2Uo.7hggddf66BOQrFgZJxJxBxUq7e5lrtjeyRSZBFo2Hq', 3, 'active', 0, 0, NULL, '2026-01-25 15:44:45', '2026-01-25 16:15:32'),
(4, 'teamleader', 'teamleader@hrnexa.com', '$2y$10$ehnctvRlHzAB.cXdwLetEeCoSbsUkAjqpSZt3DiunjSTLJGngcnoO', 4, 'active', 0, 0, NULL, '2026-01-25 15:44:45', '2026-01-25 17:48:02'),
(5, 'Bani Amin', 'bani@gmail.com', '$2y$10$OAyprX8mfOF8jC9h1q.qHOw9gApUihgx8vO.UZLe7gl1upDLkgdS6', 4, 'active', 0, 0, NULL, '2026-02-04 10:07:27', '2026-02-04 10:07:27'),
(9, 'Asad Khan', 'asadkhan@gmail.com', '$2y$10$6d072u6hV3vWgSZhgc3QU.g9Zz1obwWU.URApWGzv5zFUTYl3vH9i', 4, 'active', 0, 0, NULL, '2026-02-06 07:30:53', '2026-02-06 07:30:53');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `appraisal_cycles`
--
ALTER TABLE `appraisal_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appraisal_cycles_created_by` (`created_by`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_assets_asset_code` (`asset_code`);

--
-- Indexes for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_assignments_asset` (`asset_id`),
  ADD KEY `idx_asset_assignments_employee` (`employee_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_attendance_employee_date` (`employee_id`,`date`),
  ADD KEY `idx_attendance_employee_date` (`employee_id`,`date`),
  ADD KEY `idx_attendance_date` (`date`),
  ADD KEY `idx_attendance_approved_by` (`approved_by`);

--
-- Indexes for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_att_corr_employee` (`employee_id`),
  ADD KEY `idx_att_corr_requested_by` (`requested_by`),
  ADD KEY `idx_att_corr_approved_by` (`approved_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_user` (`user_id`),
  ADD KEY `idx_audit_logs_timestamp` (`timestamp`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_candidates_email` (`email`);

--
-- Indexes for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_candidate_applications_candidate` (`candidate_id`),
  ADD KEY `idx_candidate_applications_requisition` (`job_requisition_id`);

--
-- Indexes for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deduction_code` (`deduction_code`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_departments_head` (`head_id`),
  ADD KEY `idx_departments_parent` (`parent_id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_designations_department` (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_employees_employee_code` (`employee_code`),
  ADD UNIQUE KEY `uk_employees_email` (`email`),
  ADD KEY `idx_employees_department` (`department_id`),
  ADD KEY `idx_employees_designation` (`designation_id`),
  ADD KEY `idx_employees_manager` (`team_leader_id`);

--
-- Indexes for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_allowances_employee` (`employee_id`),
  ADD KEY `idx_employee_allowances_allowance` (`allowance_id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_deductions_employee` (`employee_id`),
  ADD KEY `idx_employee_deductions_deduction` (`deduction_id`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_documents_employee` (`employee_id`);

--
-- Indexes for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_emergency_employee` (`employee_id`);

--
-- Indexes for table `employee_goals`
--
ALTER TABLE `employee_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_goals_employee` (`employee_id`),
  ADD KEY `idx_employee_goals_kpi` (`kpi_id`),
  ADD KEY `idx_employee_goals_assigned_by` (`assigned_by`);

--
-- Indexes for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_salaries_employee` (`employee_id`),
  ADD KEY `idx_employee_salaries_structure` (`salary_structure_id`);

--
-- Indexes for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_shifts_employee` (`employee_id`),
  ADD KEY `idx_employee_shifts_shift` (`shift_id`);

--
-- Indexes for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_skills_employee` (`employee_id`),
  ADD KEY `idx_employee_skills_skill` (`skill_id`);

--
-- Indexes for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_trainings_employee` (`employee_id`),
  ADD KEY `idx_employee_trainings_program` (`training_program_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_claims`
--
ALTER TABLE `expense_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expense_claims_employee` (`employee_id`),
  ADD KEY `idx_expense_claims_category` (`category_id`),
  ADD KEY `idx_expense_claims_approved_by` (`approved_by`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_holidays_date_name` (`date`,`name`);

--
-- Indexes for table `hr_audit_logs`
--
ALTER TABLE `hr_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_interviews_candidate` (`candidate_id`),
  ADD KEY `idx_interviews_requisition` (`job_requisition_id`),
  ADD KEY `idx_interviews_interviewer` (`interviewer_id`);

--
-- Indexes for table `job_requisitions`
--
ALTER TABLE `job_requisitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_requisitions_department` (`department_id`),
  ADD KEY `idx_job_requisitions_created_by` (`created_by`);

--
-- Indexes for table `kpis`
--
ALTER TABLE `kpis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_applications_employee` (`employee_id`),
  ADD KEY `idx_leave_applications_type` (`leave_type_id`),
  ADD KEY `idx_leave_applications_approved_by` (`approved_by`);

--
-- Indexes for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leave_policies_employee` (`employee_id`),
  ADD KEY `idx_leave_policies_type` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_leave_types_name` (`name`);

--
-- Indexes for table `level_allowances`
--
ALTER TABLE `level_allowances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_level_allowance` (`level`,`allowance_name`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_name` (`module_name`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`);

--
-- Indexes for table `offboarding`
--
ALTER TABLE `offboarding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offboarding_employee` (`employee_id`);

--
-- Indexes for table `offer_letters`
--
ALTER TABLE `offer_letters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_offer_letters_candidate` (`candidate_id`),
  ADD KEY `idx_offer_letters_requisition` (`job_requisition_id`);

--
-- Indexes for table `onboarding`
--
ALTER TABLE `onboarding`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_onboarding_employee` (`employee_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payroll_runs_processed_by` (`processed_by`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payslips_payroll_run` (`payroll_run_id`),
  ADD KEY `idx_payslips_employee` (`employee_id`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_performance_reviews_employee` (`employee_id`),
  ADD KEY `idx_performance_reviews_cycle` (`appraisal_cycle_id`),
  ADD KEY `idx_performance_reviews_reviewer` (`reviewer_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_permissions_module_action` (`module`,`action`);

--
-- Indexes for table `reporting_hierarchy`
--
ALTER TABLE `reporting_hierarchy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reporting_employee` (`employee_id`),
  ADD KEY `idx_reporting_manager` (`manager_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_roles_name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `idx_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `salary_structures`
--
ALTER TABLE `salary_structures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_skills_name` (`name`);

--
-- Indexes for table `system_backups`
--
ALTER TABLE `system_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `system_holidays`
--
ALTER TABLE `system_holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_policies`
--
ALTER TABLE `system_policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_system_settings_key` (`key`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_programs`
--
ALTER TABLE `training_programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_sessions_token` (`token`),
  ADD KEY `idx_user_sessions_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `appraisal_cycles`
--
ALTER TABLE `appraisal_cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=372;

--
-- AUTO_INCREMENT for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employee_goals`
--
ALTER TABLE `employee_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `expense_claims`
--
ALTER TABLE `expense_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_audit_logs`
--
ALTER TABLE `hr_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_requisitions`
--
ALTER TABLE `job_requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kpis`
--
ALTER TABLE `kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `level_allowances`
--
ALTER TABLE `level_allowances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offboarding`
--
ALTER TABLE `offboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `offer_letters`
--
ALTER TABLE `offer_letters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `onboarding`
--
ALTER TABLE `onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `reporting_hierarchy`
--
ALTER TABLE `reporting_hierarchy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_structures`
--
ALTER TABLE `salary_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_backups`
--
ALTER TABLE `system_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_holidays`
--
ALTER TABLE `system_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `system_policies`
--
ALTER TABLE `system_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `training_programs`
--
ALTER TABLE `training_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appraisal_cycles`
--
ALTER TABLE `appraisal_cycles`
  ADD CONSTRAINT `fk_appraisal_cycles_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `fk_asset_assignments_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_asset_assignments_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance_corrections`
--
ALTER TABLE `attendance_corrections`
  ADD CONSTRAINT `fk_att_corr_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_corr_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `candidate_applications`
--
ALTER TABLE `candidate_applications`
  ADD CONSTRAINT `fk_candidate_applications_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_candidate_applications_requisition` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `compliance_reports`
--
ALTER TABLE `compliance_reports`
  ADD CONSTRAINT `compliance_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_designation` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_manager` FOREIGN KEY (`team_leader_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `employee_allowances`
--
ALTER TABLE `employee_allowances`
  ADD CONSTRAINT `fk_employee_allowances_allowance` FOREIGN KEY (`allowance_id`) REFERENCES `allowances` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_allowances_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `fk_employee_deductions_deduction` FOREIGN KEY (`deduction_id`) REFERENCES `deductions` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_deductions_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `fk_employee_documents_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  ADD CONSTRAINT `fk_employee_emergency_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_goals`
--
ALTER TABLE `employee_goals`
  ADD CONSTRAINT `fk_employee_goals_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_goals_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_goals_kpi` FOREIGN KEY (`kpi_id`) REFERENCES `kpis` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD CONSTRAINT `fk_employee_salaries_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_salaries_structure` FOREIGN KEY (`salary_structure_id`) REFERENCES `salary_structures` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD CONSTRAINT `fk_employee_shifts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_shifts_shift` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD CONSTRAINT `fk_employee_skills_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_skills_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `employee_trainings`
--
ALTER TABLE `employee_trainings`
  ADD CONSTRAINT `fk_employee_trainings_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_trainings_program` FOREIGN KEY (`training_program_id`) REFERENCES `training_programs` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `expense_claims`
--
ALTER TABLE `expense_claims`
  ADD CONSTRAINT `fk_expense_claims_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_claims_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_expense_claims_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_interviews_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_interviews_interviewer` FOREIGN KEY (`interviewer_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_interviews_requisition` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_requisitions`
--
ALTER TABLE `job_requisitions`
  ADD CONSTRAINT `fk_job_requisitions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_job_requisitions_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD CONSTRAINT `fk_leave_applications_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_applications_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_applications_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD CONSTRAINT `fk_leave_policies_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leave_policies_type` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `offboarding`
--
ALTER TABLE `offboarding`
  ADD CONSTRAINT `fk_offboarding_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `offer_letters`
--
ALTER TABLE `offer_letters`
  ADD CONSTRAINT `fk_offer_letters_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_offer_letters_requisition` FOREIGN KEY (`job_requisition_id`) REFERENCES `job_requisitions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `onboarding`
--
ALTER TABLE `onboarding`
  ADD CONSTRAINT `fk_onboarding_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD CONSTRAINT `fk_payroll_runs_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `fk_payslips_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payslips_payroll_run` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `fk_performance_reviews_cycle` FOREIGN KEY (`appraisal_cycle_id`) REFERENCES `appraisal_cycles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_performance_reviews_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_performance_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `reporting_hierarchy`
--
ALTER TABLE `reporting_hierarchy`
  ADD CONSTRAINT `fk_reporting_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reporting_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `system_backups`
--
ALTER TABLE `system_backups`
  ADD CONSTRAINT `system_backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
