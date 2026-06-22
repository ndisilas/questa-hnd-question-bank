-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 11:09 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `questa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_type` enum('exam','holiday','academic','registration','deadline') DEFAULT 'academic',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `description`, `event_date`, `event_type`, `created_at`) VALUES
(1, 'First Semester Exams Begin', 'Written examinations for first semester start today.', '2025-06-15', 'exam', '2026-04-30 13:08:39'),
(2, 'Good Friday', 'Public Holiday - No classes', '2025-04-18', 'holiday', '2026-04-30 13:08:39'),
(3, 'Easter Monday', 'Public Holiday - No classes', '2025-04-21', 'holiday', '2026-04-30 13:08:39'),
(4, 'Second Semester Begins', 'Lectures resume for second semester', '2025-05-05', 'academic', '2026-04-30 13:08:39'),
(5, 'Course Registration Deadline', 'Last day for second semester registration', '2025-05-15', 'deadline', '2026-04-30 13:08:39'),
(6, 'Mid-Semester Break', 'One week break - No classes', '2025-06-10', 'holiday', '2026-04-30 13:08:39'),
(7, 'Graduation Ceremony', 'Congratulations to the graduating class!', '2025-08-01', 'academic', '2026-04-30 13:08:39'),
(8, 'National Day', 'Cameroon National Day - No classes', '2025-10-01', 'holiday', '2026-04-30 13:08:39'),
(9, 'Matriculation Ceremony', 'Formal induction of new students', '2025-09-08', 'academic', '2026-04-30 13:08:39'),
(10, 'Second Semester Exams Begin', 'Written examinations for second semester start', '2025-07-10', 'exam', '2026-04-30 13:08:39'),
(11, 'Christmas Break', 'University closed for Christmas holidays', '2025-12-20', 'holiday', '2026-04-30 13:08:39'),
(12, 'New Academic Year Begins', 'Welcome to the 2025/2026 academic session', '2025-09-01', 'academic', '2026-04-30 13:08:39'),
(13, 'Project Submission Deadline', 'Final year project submission deadline', '2025-05-30', 'deadline', '2026-04-30 13:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_conversations`
--

CREATE TABLE `chatbot_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question` text DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `code`, `department_id`) VALUES
(1, 'Database Management Systems', 'SWE301', 1),
(2, 'Web Development', 'SWE302', 1),
(3, 'Object Oriented Programming', 'SWE303', 1),
(4, 'Network Security', 'SWE304', 1),
(5, 'Mobile App Development', 'SWE305', 1),
(6, 'tutorials', 'EDU002', 2);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `abbreviation` varchar(10) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `abbreviation`, `icon`) VALUES
(1, 'Software Engineering', 'SWE', 'fa-code'),
(2, 'Accountancy', 'ACC', 'fa-chart-line'),
(3, 'Nursing', 'NUR', 'fa-heartbeat'),
(4, 'Human Resource Management', 'HRM', 'fa-users'),
(5, 'Marketing', 'MKT', 'fa-chart-simple'),
(6, 'Computer Engineering', 'CSE', 'fa-network-wired'),
(7, 'Tourism and Hospitality', 'TOU', 'fa-umbrella-beach'),
(8, 'Journalism', 'JOU', 'fa-newspaper');

-- --------------------------------------------------------

--
-- Table structure for table `exam_dates`
--

CREATE TABLE `exam_dates` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `exam_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `exam_dates`
--

INSERT INTO `exam_dates` (`id`, `department_id`, `course_name`, `course_code`, `exam_date`, `start_time`, `end_time`, `venue`, `semester`, `academic_year`, `status`, `created_at`) VALUES
(1, 1, 'Database Management Systems', 'SWE 301', '2025-06-15', '08:00:00', '11:00:00', 'Lab 101', 1, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(2, 1, 'Web Development', 'SWE 302', '2025-06-17', '10:00:00', '13:00:00', 'Lab 102', 1, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(3, 1, 'Object Oriented Programming', 'SWE 303', '2025-06-19', '09:00:00', '12:00:00', 'Room 201', 1, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(4, 1, 'Software Project Management', 'SWE 304', '2025-06-22', '13:00:00', '16:00:00', 'Room 202', 1, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(5, 1, 'Network Security', 'SWE 305', '2025-07-10', '10:00:00', '13:00:00', 'Lab 103', 2, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(6, 1, 'Mobile App Development', 'SWE 306', '2025-07-12', '08:00:00', '11:00:00', 'Lab 101', 2, '2025/2026', 'upcoming', '2026-04-30 13:02:08'),
(7, 1, 'Artificial Intelligence', 'SWE 307', '2025-07-15', '09:00:00', '12:00:00', 'Room 203', 2, '2025/2026', 'upcoming', '2026-04-30 13:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `exam_result`
--

CREATE TABLE `exam_result` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `credit` int(11) NOT NULL DEFAULT 4,
  `ca_score` int(11) DEFAULT 0,
  `exam_score` int(11) DEFAULT 0,
  `total_score` int(11) DEFAULT 0,
  `grade` varchar(2) DEFAULT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `credit` int(11) DEFAULT 4,
  `ca_score` int(11) DEFAULT 0,
  `exam_score` int(11) DEFAULT 0,
  `total_score` int(11) DEFAULT 0,
  `grade` varchar(2) DEFAULT NULL,
  `semester` int(11) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `student_id`, `course_code`, `course_name`, `credit`, `ca_score`, `exam_score`, `total_score`, `grade`, `semester`, `academic_year`, `created_at`) VALUES
(9, 2, 'CEC 415', 'DISTRIBUTED PROGRAMMING', 4, 0, 20, 20, 'F', 1, '2025/2026', '2026-04-30 11:00:46'),
(10, 2, 'COT 401', 'ENTREPRENEURSHIP', 4, 25, 42, 67, 'B', 1, '2025/2026', '2026-04-30 11:00:46'),
(11, 2, 'CEC 409', 'INTERNET APPLICATION PROGRAMMING', 4, 25, 54, 79, 'B+', 1, '2025/2026', '2026-04-30 11:00:46'),
(12, 2, 'CEC 420', 'INTERNSHIP', 4, 0, 0, 0, 'F', 1, '2025/2026', '2026-04-30 11:00:46'),
(13, 2, 'CEC 417', 'MOBILE APPLICATION DEVELOPMENT', 4, 26, 56, 82, 'A', 1, '2025/2026', '2026-04-30 11:00:46'),
(14, 2, 'CEC 411', 'MODELING IN INFORMATION SYSTEM', 4, 25, 32, 57, 'C+', 1, '2025/2026', '2026-04-30 11:00:46'),
(15, 2, 'DCT 120', 'RESEARCH METHODOLOGY', 4, 24, 30, 54, 'C', 1, '2025/2026', '2026-04-30 11:00:46'),
(16, 2, 'CEC 413', 'SOFTWARE DEVELOPMENT', 4, 20, 42, 62, 'B', 1, '2025/2026', '2026-04-30 11:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `library_resources`
--

CREATE TABLE `library_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('ebook','article','video','guide','database') DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `author` varchar(100) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `library_resources`
--

INSERT INTO `library_resources` (`id`, `title`, `description`, `type`, `url`, `author`, `icon`) VALUES
(1, 'Database Systems: Design and Implementation', 'Comprehensive guide to database design, SQL, and database management systems.', 'ebook', '#', 'Carlos Coronel', 'fa-book'),
(2, 'Software Engineering: A Practitioner\'s Approach', 'Essential reading for software engineering students covering methodologies and best practices.', 'ebook', '#', 'Roger S. Pressman', 'fa-book'),
(3, 'Web Development with HTML, CSS, and JavaScript', 'Learn modern web development from scratch with practical examples.', 'ebook', '#', 'Jon Duckett', 'fa-book'),
(4, 'The Future of Artificial Intelligence in Education', 'Research article on how AI is transforming educational practices and assessment.', 'article', '#', 'EdTech Review', 'fa-file-alt'),
(5, 'Sustainable Development Goals and Higher Education', 'How universities are contributing to SDGs through research and community engagement.', 'article', '#', 'Higher Education Quarterly', 'fa-file-alt'),
(6, 'Introduction to Object-Oriented Programming', 'Video tutorial covering OOP concepts: classes, objects, inheritance, polymorphism.', 'video', '#', 'Programming Master', 'fa-video'),
(7, 'How to Ace Your Exams: Study Strategies', 'Proven techniques for effective studying and exam preparation.', 'video', '#', 'Study Tips Hub', 'fa-video'),
(8, 'Exam Preparation Guide: Tips and Techniques', 'Comprehensive guide to preparing for HND examinations effectively.', 'guide', '#', 'Academic Success', 'fa-graduation-cap'),
(9, 'Research Methodology Handbook', 'Step-by-step guide to conducting academic research and writing projects.', 'guide', '#', 'Research Dept', 'fa-graduation-cap'),
(10, 'Google Scholar', 'Search engine for academic papers, theses, books, and conference proceedings.', 'database', 'https://scholar.google.com', 'Google', 'fa-database'),
(11, 'ResearchGate', 'Network where researchers share papers and collaborate.', 'database', 'https://www.researchgate.net', 'ResearchGate', 'fa-database'),
(12, 'IEEE Xplore', 'Digital library for technical literature in engineering and computer science.', 'database', 'https://ieeexplore.ieee.org', 'IEEE', 'fa-database');

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `year_of_study` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `category` enum('academic','event','announcement','deadline','achievement') DEFAULT 'announcement',
  `featured` tinyint(4) DEFAULT 0,
  `author_name` varchar(100) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `excerpt`, `category`, `featured`, `author_name`, `view_count`, `created_at`) VALUES
(1, 'First Semester Examinations Schedule Released', 'The academic board has officially released the timetable for the First Semester examinations for the 2025/2026 academic session. All students are advised to check their respective departments for specific examination dates and venues. Important: Students must present their student ID cards at all examination venues. Any student found without a valid ID will not be allowed to sit for the exam. The examination period runs from June 15th to July 20th, 2025.', 'The academic board has officially released the timetable for First Semester examinations.', 'academic', 1, 'Academic Board', 0, '2026-04-30 13:24:54'),
(2, 'HIPTEX Announces 3rd Annual Career Fair 2025', 'HIPTEX is pleased to announce the 3rd Annual Career Fair, scheduled for May 10th, 2025. This event brings together top employers from across Cameroon to connect with our talented students and graduates. Participating companies include MTN Cameroon, Orange, Afriland First Bank, and many more. Students are encouraged to bring multiple copies of their CVs and dress professionally. Registration is free but required. Register at the Student Affairs Office before May 5th.', 'Join us for the biggest career event of the year! Top employers will be on campus to recruit our graduates.', 'event', 1, 'Student Affairs', 0, '2026-04-30 13:24:54'),
(3, 'Course Registration Deadline Extension', 'Due to technical challenges experienced by some students during the registration process, the management has approved a 2-week extension for course registration. The new deadline is May 15th, 2025. Students who have not yet completed their registration are advised to do so immediately. Late registration will attract a penalty fee of 10,000 FCFA.', 'The management has approved a 2-week extension for course registration.', 'deadline', 0, 'Registrar\'s Office', 0, '2026-04-30 13:24:54'),
(4, 'HIPTEX Students Win National Coding Competition', 'Congratulations to our Software Engineering team who emerged first place at the Cameroon National Coding Challenge 2025! The team developed an innovative health-tech solution that impressed the judges. The competition saw participation from over 50 universities across Cameroon. This achievement brings great honor to HIPTEX and showcases the quality of our tech education.', 'Our Software Engineering team emerged first place at the Cameroon National Coding Challenge 2025.', 'achievement', 0, 'Dean\'s Office', 0, '2026-04-30 13:24:54'),
(5, 'Library Extended Hours During Exam Period', 'To support students during the examination period, the university library will extend its operating hours. Starting June 1st, the library will be open from 7:00 AM to 10:00 PM daily. The 24-hour study room will be available for students who prefer late-night study sessions. All library resources are accessible during these hours.', 'The university library will remain open until 10 PM daily to accommodate students preparing for exams.', 'announcement', 0, 'Library Services', 0, '2026-04-30 13:24:54'),
(6, 'Scholarship Applications Now Open', 'The HIPTEX Merit Scholarship is now open for applications for the 2025/2026 academic session. Eligible students must have a minimum GPA of 3.5. Application forms are available at the Financial Aid Office. The deadline for submission is May 30th, 2025.', 'Apply for the HIPTEX Merit Scholarship for the 2025/2026 academic session.', 'deadline', 0, 'Financial Aid Office', 0, '2026-04-30 13:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `question_papers`
--

CREATE TABLE `question_papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `year` varchar(10) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `solution_file_path` varchar(500) DEFAULT NULL,
  `has_answer` tinyint(4) DEFAULT 0,
  `tags` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp(),
  `downloads` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question_papers`
--

INSERT INTO `question_papers` (`id`, `title`, `course_id`, `year`, `semester`, `file_path`, `solution_file_path`, `has_answer`, `tags`, `notes`, `uploaded_by`, `upload_date`, `downloads`) VALUES
(1, 'Database Management Systems Past Paper', 1, '2024', '2', 'uploads/papers/dbms_2024.pdf', NULL, 0, NULL, NULL, 1, '2026-04-30 11:10:33', 0),
(2, 'Web Development Past Paper', 2, '2024', '1', 'uploads/papers/web_2024.pdf', NULL, 0, NULL, NULL, 1, '2026-04-30 11:10:33', 0),
(3, 'tutorial', 6, '2025', '1', '../uploads/papers/1777981810_tutorial.pdf', NULL, 0, NULL, NULL, 1, '2026-05-05 12:50:10', 0);

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `course_name` varchar(200) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `day_of_week` varchar(15) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `lecturer` varchar(100) DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `department_id`, `course_name`, `course_code`, `day_of_week`, `start_time`, `end_time`, `lecturer`, `venue`, `created_at`) VALUES
(1, 1, 'Database Management Systems', 'SWE 301', 'Monday', '08:00:00', '10:00:00', 'Dr. Paul Ngu', 'Lab 101', '2026-04-30 11:42:17'),
(2, 1, 'Web Development', 'SWE 302', 'Monday', '10:00:00', '12:00:00', 'Mr. Michael Tita', 'Lab 102', '2026-04-30 11:42:17'),
(3, 1, 'Object Oriented Programming', 'SWE 303', 'Tuesday', '09:00:00', '11:00:00', 'Dr. Sarah Kemayou', 'Room 201', '2026-04-30 11:42:17'),
(4, 1, 'Software Project Management', 'SWE 304', 'Tuesday', '13:00:00', '15:00:00', 'Prof. John Atem', 'Room 202', '2026-04-30 11:42:17'),
(5, 1, 'Network Security', 'SWE 305', 'Wednesday', '10:00:00', '12:00:00', 'Dr. Emmanuel Fru', 'Lab 103', '2026-04-30 11:42:17'),
(6, 1, 'Mobile App Development', 'SWE 306', 'Thursday', '08:00:00', '10:00:00', 'Mr. Collins Ndifor', 'Lab 101', '2026-04-30 11:42:17'),
(7, 1, 'Artificial Intelligence', 'SWE 307', 'Friday', '09:00:00', '11:00:00', 'Dr. Vanessa Mboua', 'Room 203', '2026-04-30 11:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student') DEFAULT 'student',
  `matricule` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `matricule`, `department_id`, `avatar`, `created_at`) VALUES
(1, 'Administrator', 'admin@questa.com', '$2y$10$JcMKv9fam29zWg83AUHRUuARMkvQeq2wb9OEtQBAA9rb3rC/q.Bp.', 'admin', NULL, 1, 'uploads/avatars/1777540754_1.jpg', '2026-04-30 09:11:18'),
(2, 'Demo Student', 'demo@questa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'DEMO/2025/001', 1, NULL, '2026-04-30 11:55:44'),
(4, 'Ndi Silas', 'demo2@questa.com', '$2y$10$LFLvdRQmpI726uSCBwb.he15eH9SVyNekLAMFCXkMimR.31wPcbU.', 'student', 'HIPTEX/SWE/001', 1, 'uploads/avatars/1777979370_4.jpg', '2026-04-30 11:58:26'),
(5, 'Nformi Silas', 'nformisilas95@questa.com', '$2y$10$p.NDq7cZK2Kl48zc74BRueLw8yoOoED7Mt9bAbuZpBUuYO2Igd78.', 'student', 'HIPTEX/SWE/002', 5, NULL, '2026-05-05 12:41:16'),
(6, 'ndi', 'ndi@questa.com', '$2y$10$pIPUR/m2DV5b30a6v6JYFu6z0EepY7R4udyu3XFF/J4KHGqOsN6Eu', 'student', 'HIPTEX/ACC/002', 2, NULL, '2026-05-05 12:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `viewed_at` datetime DEFAULT NULL,
  `downloaded_at` datetime DEFAULT NULL,
  `attempted` tinyint(4) DEFAULT 0,
  `score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_dates`
--
ALTER TABLE `exam_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `library_resources`
--
ALTER TABLE `library_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricule` (`matricule`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `paper_id` (`paper_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `exam_dates`
--
ALTER TABLE `exam_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `exam_result`
--
ALTER TABLE `exam_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `library_resources`
--
ALTER TABLE `library_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mentors`
--
ALTER TABLE `mentors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `question_papers`
--
ALTER TABLE `question_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chatbot_conversations`
--
ALTER TABLE `chatbot_conversations`
  ADD CONSTRAINT `chatbot_conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_dates`
--
ALTER TABLE `exam_dates`
  ADD CONSTRAINT `exam_dates_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD CONSTRAINT `exam_result_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentors`
--
ALTER TABLE `mentors`
  ADD CONSTRAINT `mentors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD CONSTRAINT `question_papers_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_papers_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetable`
--
ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`paper_id`) REFERENCES `question_papers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
