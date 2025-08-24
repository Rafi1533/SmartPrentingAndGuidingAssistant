-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2025 at 12:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `family_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement_stories`
--

CREATE TABLE `achievement_stories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievement_stories`
--

INSERT INTO `achievement_stories` (`id`, `user_id`, `title`, `description`, `media_path`, `is_published`, `created_at`) VALUES
(1, 1, 'Din Sesh', 'Tmi hete chole gele uttor e ami shohorer sesh tuku bhalobasha niye dariye roilam.', 'uploads/achievements/6899fa7924786_6084624316944727767_121.jpg', 1, '2025-08-11 14:13:13');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  `nid_card_photo` varchar(255) NOT NULL,
  `nid_number` varchar(50) NOT NULL,
  `admin_photo` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `email`, `phone`, `area`, `nid_card_photo`, `nid_number`, `admin_photo`, `password`, `created_at`) VALUES
(1, 'Abdullah Al Rafi', 'Rafi', 'rafiabdullah1507@gmail.com', '01842542469', 'Dhaka', 'uploads/nid_photos/6899d7a342901_Screenshot 2024-12-03 012921.png', '99999999999999', 'uploads/admin_photos/6899d7a34290d_51792fd9-57f9-4cbf-be1f-1883c8e45bf2.png', '$2y$10$pQJPD6m5WENdkXyPUQwbSeKgnh1vSk7YaBpia6GRiaWRZsBgtupAm', '2025-08-11 11:44:35'),
(2, 'Abdullah Al', 'Rafi', '2002015@icte.bdu.ac.bd', '018425424699', 'Dhaka', 'uploads/nid_photos/689bbe11dbf99_ef029bd0-46a2-476b-99fa-472ab5fcb2f3.png', '999999', 'uploads/admin_photos/689bbe11dbfa3_ef029bd0-46a2-476b-99fa-472ab5fcb2f3.png', '$2y$10$rJ6Y8ecxJ55wRuj7FUmOG.mzhDQBMgkpj7St3mFEzyzmnr9VcRXN2', '2025-08-12 22:20:01');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','completed','canceled') DEFAULT 'pending',
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `session_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `link`, `created_at`) VALUES
(1, 1, 1, 1, '2025-08-14', '10:01:00', 'confirmed', 'https://meet.google.com/gvd-rbpd-des?ijlm=1745239686597&hs=187&adhoc=1', '2025-08-12 19:49:58'),
(2, 1, 1, 1, '2025-08-14', '03:51:00', 'confirmed', 'https://meet.google.com/gvd-rbpd-des?ijlm=1745239686597&hs=187&adhoc=1', '2025-08-12 19:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `autism_results`
--

CREATE TABLE `autism_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `section` varchar(20) NOT NULL,
  `red_flags` int(11) NOT NULL,
  `risk_level` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `autism_results`
--

INSERT INTO `autism_results` (`id`, `user_id`, `section`, `red_flags`, `risk_level`, `date`) VALUES
(1, 1, 'infant', 5, 'Moderate level of en', '2025-08-12 18:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `emergencies`
--

CREATE TABLE `emergencies` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `media_path` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emergencies`
--

INSERT INTO `emergencies` (`id`, `parent_id`, `name`, `location`, `phone`, `description`, `media_path`, `latitude`, `longitude`, `created_at`) VALUES
(5, 1, 'Abdullah Al Rafi', 'Rumaisa General Hospital, Kaliakair Bazar', '01842542469', 'help', './uploaded_emergencies/bec31fdf321d788c2509ca99e530f5f5.jpg', 23.7731840, 90.4003584, '2025-08-11 12:55:34'),
(6, 1, 'Abdullah Al Rafi', 'Rumaisa General Hospital, Kaliakair Bazar', '01842542469', 'help me', './uploaded_emergencies/8771dedae0e97f16f71c73097b0a423f.png', 23.7731840, 90.4003584, '2025-08-11 12:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `group_counselings`
--

CREATE TABLE `group_counselings` (
  `id` int(11) NOT NULL,
  `details` text NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_counselings`
--

INSERT INTO `group_counselings` (`id`, `details`, `doctor_name`, `start_time`, `end_time`, `link`, `created_at`) VALUES
(1, 'Okay', 'Rafi', '2025-08-13 01:47:00', '2025-08-13 02:06:00', 'https://meet.google.com/vda-tsyi-nmd', '2025-08-12 19:48:36');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','shipping','delivered') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT 'cash on delivery',
  `phone_number` varchar(20) NOT NULL,
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `total_amount`, `order_date`, `status`, `payment_method`, `phone_number`, `location`) VALUES
(1, 1, 1, 1, 50.00, '2025-08-12 08:09:37', 'delivered', 'cash on delivery', '', ''),
(2, 1, 1, 4, 200.00, '2025-08-12 18:01:16', 'delivered', 'cash on delivery', '', ''),
(3, 1, 1, 1, 50.00, '2025-08-12 18:35:14', 'delivered', 'cash on delivery', '01842542469', 'Rumaisa General Hospital, kaliakair Bazar');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `screenshot` varchar(255) NOT NULL,
  `status` enum('pending','received') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `bkash` varchar(20) DEFAULT NULL,
  `rocket` varchar(20) DEFAULT NULL,
  `nagad` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `bkash`, `rocket`, `nagad`) VALUES
(1, '0123456789', '0123456789', '0123456789');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `details`, `image`, `video`, `price`, `quantity`) VALUES
(1, 'Baby', 'Baby Green Screen', 'uploads/UFTB.png', 'uploads/videoplayback (1) (1).mp4', 50.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `title`, `details`, `created_at`) VALUES
(1, 'How are you?', 'Not FIne', '2025-08-12 19:46:26'),
(2, 'How are you 2', 'not fine as well', '2025-08-12 19:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `session_doctors`
--

CREATE TABLE `session_doctors` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `time_slot` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_doctors`
--

INSERT INTO `session_doctors` (`id`, `session_id`, `doctor_name`, `time_slot`) VALUES
(1, 1, 'Rafi', '10:00 AM- 11:00AM'),
(2, 2, 'Toton Shihab', '10:00 AM- 11:00AM');

-- --------------------------------------------------------

--
-- Table structure for table `teaching_aid_items`
--

CREATE TABLE `teaching_aid_items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teaching_aid_items`
--

INSERT INTO `teaching_aid_items` (`id`, `name`, `price`, `description`, `unit`, `unit_price`, `picture`, `video`, `created_at`) VALUES
(1, 'Planet Earth', 20.00, 'a planet globe', '1', 20.00, 'uploads/teaching_aids/pictures/689bc0f91a604_বিদেশি.png', 'uploads/teaching_aids/videos/689bc0f91ac80_Red Photo Raised Hand Human Rights Social Media Graphic Instagram Post_2.mp4', '2025-08-12 22:32:25');

-- --------------------------------------------------------

--
-- Table structure for table `teaching_aid_orders`
--

CREATE TABLE `teaching_aid_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `teaching_aid_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `delivery_number` varchar(50) NOT NULL,
  `delivery_location` varchar(255) NOT NULL,
  `status` enum('Pending','Processing','Shipping','Delivered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `therapy_autism_types`
--

CREATE TABLE `therapy_autism_types` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapy_autism_types`
--

INSERT INTO `therapy_autism_types` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Autism Type 1', 'Autism Type 1 is a common form of autism. This type primarily affects social interactions and communication.', '2025-08-12 20:26:57'),
(2, 'Autism Type 2', 'Autism Type 2 is characterized by limited verbal communication and repetitive behaviors.', '2025-08-12 20:27:24'),
(3, 'Autism Type 3', 'Autism Type 3 often involves higher cognitive abilities, but with challenges in emotional regulation and social skills.', '2025-08-12 20:27:49');

-- --------------------------------------------------------

--
-- Table structure for table `therapy_payments`
--

CREATE TABLE `therapy_payments` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 200.00,
  `tx_id` varchar(255) NOT NULL,
  `tx_screenshot_path` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Received','Rejected') DEFAULT 'Pending',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapy_payments`
--

INSERT INTO `therapy_payments` (`id`, `session_id`, `user_id`, `amount`, `tx_id`, `tx_screenshot_path`, `status`, `uploaded_at`, `received_at`) VALUES
(1, 1, 1, 200.00, '34566689', 'uploads/session_receipts/pay_689ba4c55ad90.png', 'Received', '2025-08-12 20:32:05', '2025-08-12 20:32:17'),
(2, 3, 1, 200.00, 'dfsdfsdf', 'uploads/session_receipts/pay_689ba90e4a2cf.png', 'Received', '2025-08-12 20:50:22', '2025-08-12 20:50:41');

-- --------------------------------------------------------

--
-- Table structure for table `therapy_requests`
--

CREATE TABLE `therapy_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `autism_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Assigned','Cancelled','Paid','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapy_requests`
--

INSERT INTO `therapy_requests` (`id`, `user_id`, `autism_type_id`, `title`, `description`, `status`, `created_at`) VALUES
(1, 1, 1, 'help', 'man', 'Assigned', '2025-08-12 20:28:45'),
(2, 1, 1, 'help me again', 'dead', 'Assigned', '2025-08-12 20:46:13'),
(3, 1, 2, 'help me more bro', 'aaaaaa', 'Pending', '2025-08-12 20:46:31'),
(4, 1, 1, 'fdgfdf', 'tytuyfdutufy', 'Assigned', '2025-08-12 20:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `therapy_sessions`
--

CREATE TABLE `therapy_sessions` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `autism_type_id` int(11) NOT NULL,
  `doctor_name` varchar(255) DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `video_call_link` varchar(500) DEFAULT NULL,
  `bkash_number` varchar(50) DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapy_sessions`
--

INSERT INTO `therapy_sessions` (`id`, `request_id`, `user_id`, `autism_type_id`, `doctor_name`, `start_datetime`, `end_datetime`, `video_call_link`, `bkash_number`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 'Rafi', '2025-08-13 02:29:00', '2025-08-13 03:29:00', 'https://meet.google.com/gad-sqth-aug', '+8801842542569', 'Scheduled', '2025-08-12 20:30:05'),
(2, 2, 1, 1, 'RAfi', '2025-08-13 02:47:00', '2025-08-13 04:47:00', 'https://meet.google.com/gad-sqth-aug', '+8801842542569', 'Scheduled', '2025-08-12 20:47:52'),
(3, 4, 1, 1, 'toton Rafi', '2025-08-13 02:49:00', '2025-08-13 04:49:00', 'https://meet.google.com/gad-sqth-aug', '+8801842542569', 'Scheduled', '2025-08-12 20:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `therapy_tutorials`
--

CREATE TABLE `therapy_tutorials` (
  `id` int(11) NOT NULL,
  `autism_type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `video_url` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `therapy_tutorials`
--

INSERT INTO `therapy_tutorials` (`id`, `autism_type_id`, `title`, `details`, `video_url`, `created_at`) VALUES
(1, 1, 'How to play.', '........', '............', '2025-08-12 20:28:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `number_of_kids` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `gender`, `number_of_kids`, `email`, `phone`, `area`, `password`) VALUES
(1, 'Abdullah', 'Rafi', 'Male', 0, 'rafiabdullah1507@gmail.com', '01842542469', 'Rangpur', '$2y$10$lHYilIHuO76ISqvLD/4bdeAmar2C7G1woST4Iv4EnMFckTQcccNx6'),
(2, 'Abdullah Al ', 'Rafi', 'Male', 0, '2002015@icte.bdu.ac.bd', '+8801842542468', 'Rangpur', '$2y$10$7Qoi5UaGgHYVMD6B/czLju6YbRk1SpOsnToZgu/xus9K4TX1Sxtfa'),
(3, 'Abdullah Al', 'Rofiman', 'Male', 0, 'rafiabdullah411@gmail.com', '+8801842542469', 'Rangpur', '$2y$10$DiCuEWlrGb90r1D/iPsnIOQIjMXNvRJw04XBdDSYTRYPp9JGIvqM6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement_stories`
--
ALTER TABLE `achievement_stories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `autism_results`
--
ALTER TABLE `autism_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emergencies`
--
ALTER TABLE `emergencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_counselings`
--
ALTER TABLE `group_counselings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `session_doctors`
--
ALTER TABLE `session_doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `teaching_aid_items`
--
ALTER TABLE `teaching_aid_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teaching_aid_orders`
--
ALTER TABLE `teaching_aid_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teaching_aid_id` (`teaching_aid_id`);

--
-- Indexes for table `therapy_autism_types`
--
ALTER TABLE `therapy_autism_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `therapy_payments`
--
ALTER TABLE `therapy_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `therapy_requests`
--
ALTER TABLE `therapy_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `autism_type_id` (`autism_type_id`);

--
-- Indexes for table `therapy_sessions`
--
ALTER TABLE `therapy_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `autism_type_id` (`autism_type_id`);

--
-- Indexes for table `therapy_tutorials`
--
ALTER TABLE `therapy_tutorials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autism_type_id` (`autism_type_id`);

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
-- AUTO_INCREMENT for table `achievement_stories`
--
ALTER TABLE `achievement_stories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `autism_results`
--
ALTER TABLE `autism_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `emergencies`
--
ALTER TABLE `emergencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `group_counselings`
--
ALTER TABLE `group_counselings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `session_doctors`
--
ALTER TABLE `session_doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teaching_aid_items`
--
ALTER TABLE `teaching_aid_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `teaching_aid_orders`
--
ALTER TABLE `teaching_aid_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `therapy_autism_types`
--
ALTER TABLE `therapy_autism_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `therapy_payments`
--
ALTER TABLE `therapy_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `therapy_requests`
--
ALTER TABLE `therapy_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `therapy_sessions`
--
ALTER TABLE `therapy_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `therapy_tutorials`
--
ALTER TABLE `therapy_tutorials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievement_stories`
--
ALTER TABLE `achievement_stories`
  ADD CONSTRAINT `achievement_stories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `session_doctors` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `session_doctors`
--
ALTER TABLE `session_doctors`
  ADD CONSTRAINT `session_doctors_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`);

--
-- Constraints for table `teaching_aid_orders`
--
ALTER TABLE `teaching_aid_orders`
  ADD CONSTRAINT `teaching_aid_orders_ibfk_1` FOREIGN KEY (`teaching_aid_id`) REFERENCES `teaching_aid_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapy_payments`
--
ALTER TABLE `therapy_payments`
  ADD CONSTRAINT `therapy_payments_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `therapy_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `therapy_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapy_requests`
--
ALTER TABLE `therapy_requests`
  ADD CONSTRAINT `therapy_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `therapy_requests_ibfk_2` FOREIGN KEY (`autism_type_id`) REFERENCES `therapy_autism_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapy_sessions`
--
ALTER TABLE `therapy_sessions`
  ADD CONSTRAINT `therapy_sessions_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `therapy_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `therapy_sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `therapy_sessions_ibfk_3` FOREIGN KEY (`autism_type_id`) REFERENCES `therapy_autism_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `therapy_tutorials`
--
ALTER TABLE `therapy_tutorials`
  ADD CONSTRAINT `therapy_tutorials_ibfk_1` FOREIGN KEY (`autism_type_id`) REFERENCES `therapy_autism_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
