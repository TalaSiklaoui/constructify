-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 01:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `constructify_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_features`
--

CREATE TABLE `about_features` (
  `id` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_features`
--

INSERT INTO `about_features` (`id`, `icon`, `title`, `description`, `display_order`) VALUES
(1, 'shield-check', 'Licensed & Insured', 'Fully certified with comprehensive coverage for every project we undertake.', 1),
(2, 'clock', 'On-Time Delivery', 'Proven track record of completing projects within schedule and budget.', 2),
(3, 'users', 'Expert Workforce', 'Over 120 skilled professionals across all construction disciplines.', 3),
(4, 'trophy', 'Award Winning', 'Recognized with 35+ industry awards for quality and innovation.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `about_section`
--

CREATE TABLE `about_section` (
  `id` int(11) NOT NULL,
  `small_heading` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `second_image` varchar(255) DEFAULT NULL,
  `experience_number` varchar(20) DEFAULT NULL,
  `experience_text` varchar(100) DEFAULT NULL,
  `first_button_text` varchar(100) DEFAULT NULL,
  `second_button_text` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_section`
--

INSERT INTO `about_section` (`id`, `small_heading`, `title`, `description`, `main_image`, `second_image`, `experience_number`, `experience_text`, `first_button_text`, `second_button_text`) VALUES
(1, 'ABOUT CONSTRUCTIFY', 'We Build With Precision, Passion & Purpose', 'Since 1998, we have been delivering world-class construction services across residential, commercial, and infrastructure sectors. Our commitment to quality craftsmanship and client satisfaction drives everything we do.', 'uploads/about/019611ef03348edc96e62848c1533c59.jpg', 'uploads/about/f93dcf2b2ef9e8c65f08c5c5b347ffaf.jpg', '25+', 'Years of Experience', 'Start Your Project', 'View Our Work');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$.cK2P5dJUGliZ5deJAbiUukL9YKJJh1qafrTuiaMZ6DZk0Wpv8.tC');

-- --------------------------------------------------------

--
-- Table structure for table `home_counters`
--

CREATE TABLE `home_counters` (
  `id` int(11) NOT NULL,
  `counter_number` int(11) NOT NULL,
  `counter_suffix` varchar(10) DEFAULT NULL,
  `counter_label` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_counters`
--

INSERT INTO `home_counters` (`id`, `counter_number`, `counter_suffix`, `counter_label`, `display_order`) VALUES
(1, 25, '+', 'Years Experience', 1),
(2, 850, '+', 'Projects Completed', 2),
(3, 120, '+', 'Team Experts', 3),
(4, 35, '+', 'Industry Awards', 4);

-- --------------------------------------------------------

--
-- Table structure for table `home_section`
--

CREATE TABLE `home_section` (
  `id` int(11) NOT NULL,
  `small_heading` varchar(255) DEFAULT NULL,
  `title_line1` varchar(255) DEFAULT NULL,
  `title_line2` varchar(255) DEFAULT NULL,
  `highlighted_text` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `background_image` varchar(255) DEFAULT NULL,
  `first_button_text` varchar(100) DEFAULT NULL,
  `second_button_text` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_section`
--

INSERT INTO `home_section` (`id`, `small_heading`, `title_line1`, `title_line2`, `highlighted_text`, `description`, `background_image`, `first_button_text`, `second_button_text`) VALUES
(1, 'Trusted Construction Partner Since 1998', 'Building Tomorrow\'s', 'Infrastructure', 'Today', 'Voluptatem accusantium doloremque laudantium totam rem aperiam eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae.', 'uploads/home/756de62d7fd9522822a2541a402d13cf.jpg', 'Explore Our Projects', 'Request a Quote');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `category`, `description`, `cover_image`, `display_order`) VALUES
(1, 'Sunset Ridge Villa', 'Residential', 'Premium residential complex with contemporary architecture', 'uploads/projects/9763f3c0c44f6c5b5b8a2c5528a2fcbe.jpg', 1),
(2, 'Metro Business Hub', 'Commercial', 'State-of-the-art office complex in the city center', 'uploads/projects/7e7e3b523487e341c3512b88ba42a6fe.jpg', 2),
(3, 'Riverside Highway Bridge', 'Infrastructure', 'Major bridge connecting the northern corridor', 'uploads/projects/acecbe31abaabfb6a198f6f0982b8eb9.jpg', 3),
(4, 'Lakeview Apartments', 'Residential', 'Modern lakeside living with panoramic views', 'uploads/projects/4616321132ae5ad070abff07ddacaf6e.jpg', 4),
(5, 'Central Shopping Plaza', 'Commercial', 'Multi-level retail destination with modern amenities', 'uploads/projects/3005c1d966593fea37602d4c45215050.jpg', 5),
(6, 'Northern Rail Terminal', 'Infrastructure', 'Multi-modal transportation hub for the metro area', 'uploads/projects/07d416b1f707c4633f84d507edbc4e08.jpg', 6);

-- --------------------------------------------------------

--
-- Table structure for table `projects_section`
--

CREATE TABLE `projects_section` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects_section`
--

INSERT INTO `projects_section` (`id`, `title`, `description`) VALUES
(1, 'Our Projects', 'Excepteur sint occaecat cupidatat non proident sunt in culpa qui officia deserunt mollit anim id est laborum');

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

CREATE TABLE `project_images` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_images`
--

INSERT INTO `project_images` (`id`, `project_id`, `image_path`, `display_order`) VALUES
(15, 1, 'uploads/projects/6fee3151d45d7a414e7cd12b7962d5be.jpg', 1),
(16, 1, 'uploads/projects/d567401e153b60b000f164dc04d91036.png', 2),
(17, 2, 'uploads/projects/ec0d1d019b5fd661785e7e7e90147594.jpg', 1),
(18, 2, 'uploads/projects/e0f8b72d185a9efeaddf7887c9151e03.jpg', 2),
(19, 3, 'uploads/projects/bb4f63cec261402113f203bf5bc24dc7.jpg', 1),
(20, 3, 'uploads/projects/9f7dafc654cf002129f2773d65797a8f.jpg', 2),
(21, 4, 'uploads/projects/a37835f19647324268006388e0cafee8.jpg', 1),
(22, 4, 'uploads/projects/3bb57ae7ff30ea35f161ee885ad83445.jpg', 2),
(23, 5, 'uploads/projects/3bd247f295692547ddec51c3f7e80103.jpg', 1),
(24, 5, 'uploads/projects/68c127004a0c59869bdd6e328b7d7ba6.jpg', 2),
(25, 6, 'uploads/projects/63dd225a72b00660416820316e93f574.jpg', 1),
(26, 6, 'uploads/projects/aef44b7dfa7920a9324c07fa3f671abe.jpg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `icon`, `title`, `description`, `display_order`) VALUES
(1, 'house', 'Residential Building', 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet consectetur adipisci velit.', 1),
(2, 'building-2', 'Commercial Projects', 'Ut enim ad minima veniam quis nostrum exercitationem ullam corporis suscipit laboriosam.', 2),
(3, 'hammer', 'Renovation & Remodeling', 'Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae.', 3),
(4, 'signpost', 'Road & Infrastructure', 'Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet.', 4),
(5, 'clipboard-list', 'Project Management', 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit sed quia.', 5),
(6, 'ruler', 'Architecture & Design', 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium.', 6);

-- --------------------------------------------------------

--
-- Table structure for table `services_section`
--

CREATE TABLE `services_section` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `image_title` varchar(255) DEFAULT NULL,
  `image_button_text` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_section`
--

INSERT INTO `services_section` (`id`, `title`, `description`, `main_image`, `image_title`, `image_button_text`) VALUES
(1, 'Our Services', 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur', 'uploads/services/6b8843f3d9ac58e24a5bb3ad1725a67d.jpg', 'We Deliver Excellence in Every Project', 'Get a Free Consultation');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `estimate_button_text` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `company_name`, `logo`, `phone_number`, `estimate_button_text`) VALUES
(1, 'Constructify', 'uploads/logos/1f00c6d6aced27e2671fd9430bbb0807.png', '+1 (555) 234-6789', 'Get Estimate');

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `id` int(11) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `counter_number` int(11) NOT NULL,
  `counter_suffix` varchar(10) DEFAULT NULL,
  `counter_label` varchar(100) DEFAULT NULL,
  `display_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`id`, `icon`, `counter_number`, `counter_suffix`, `counter_label`, `display_order`) VALUES
(1, 'building-2', 850, '+', 'Projects Delivered', 1),
(2, 'smile', 620, '+', 'Satisfied Clients', 2),
(3, 'users', 120, '+', 'Skilled Workers', 3),
(4, 'award', 35, '+', 'Industry Awards', 4);

-- --------------------------------------------------------

--
-- Table structure for table `statistics_section`
--

CREATE TABLE `statistics_section` (
  `id` int(11) NOT NULL,
  `small_heading` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistics_section`
--

INSERT INTO `statistics_section` (`id`, `small_heading`, `title`, `description`, `button_text`) VALUES
(1, 'OUR TRACK RECORD', 'Numbers That Speak for Our Commitment', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium totam rem aperiam eaque ipsa quae ab illo inventore.', 'View Our Projects');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_features`
--
ALTER TABLE `about_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_section`
--
ALTER TABLE `about_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `home_counters`
--
ALTER TABLE `home_counters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_section`
--
ALTER TABLE `home_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects_section`
--
ALTER TABLE `projects_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services_section`
--
ALTER TABLE `services_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `statistics_section`
--
ALTER TABLE `statistics_section`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_features`
--
ALTER TABLE `about_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `about_section`
--
ALTER TABLE `about_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `home_counters`
--
ALTER TABLE `home_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `home_section`
--
ALTER TABLE `home_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projects_section`
--
ALTER TABLE `projects_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services_section`
--
ALTER TABLE `services_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `statistics_section`
--
ALTER TABLE `statistics_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_images`
--
ALTER TABLE `project_images`
  ADD CONSTRAINT `project_images_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
