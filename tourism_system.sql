-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 03, 2025 at 03:18 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tourism_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('Super Admin','Admin','Manager') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Admin',
  `status` enum('Active','Inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@tourism.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'Super Admin', 'Active', '2025-07-04 15:40:22', '2025-07-06 05:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `booking_reference` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int DEFAULT NULL,
  `tour_id` int DEFAULT NULL,
  `number_of_passengers` int DEFAULT '1',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `booking_status` enum('Pending','Confirmed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `payment_status` enum('Pending','Paid','Refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `processed_by` int DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_reference`, `customer_id`, `tour_id`, `number_of_passengers`, `total_amount`, `payment_image`, `payment_method`, `booking_status`, `payment_status`, `admin_notes`, `processed_by`, `processed_at`, `booking_date`) VALUES
(2, 'TUR202507061671', 2, 1, 3, 897.00, NULL, NULL, 'Confirmed', 'Pending', '', 1, '2025-07-06 04:01:27', '2025-07-06 03:47:40'),
(3, 'TUR202507061920', 3, 1, 4, 1196.00, NULL, NULL, 'Confirmed', 'Pending', '', 1, '2025-07-06 12:06:33', '2025-07-06 06:56:40'),
(4, 'TUR202507063232', 3, 1, 4, 1196.00, NULL, NULL, 'Confirmed', 'Pending', '', 1, '2025-07-06 12:06:36', '2025-07-06 06:56:40'),
(5, 'TUR202508163559', 4, 7, 1, 60000.00, NULL, NULL, 'Confirmed', 'Pending', '', 1, '2025-08-16 04:09:56', '2025-08-16 04:02:16'),
(6, 'TUR202508166087', 4, 7, 1, 60000.00, NULL, NULL, 'Confirmed', 'Pending', '', 1, '2025-08-16 04:09:46', '2025-08-16 04:03:16');

-- --------------------------------------------------------

--
-- Table structure for table `bus_types`
--

CREATE TABLE `bus_types` (
  `id` int NOT NULL,
  `type_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `capacity` int NOT NULL,
  `amenities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `price_per_km` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bus_types`
--

INSERT INTO `bus_types` (`id`, `type_name`, `capacity`, `amenities`, `price_per_km`) VALUES
(1, 'Standard Bus', 45, 'Air conditioning, comfortable seats, entertainment system', 2.50),
(2, 'Luxury Bus', 30, 'Reclining seats, WiFi, refreshments, personal entertainment', 4.00),
(3, 'VIP Bus', 20, 'Premium leather seats, individual screens, meals included, extra legroom', 6.50);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `password`, `email_verified`, `verification_token`, `phone`, `address`, `created_at`) VALUES
(1, 'Jhon', 'jhonmin@gmail.com', NULL, 0, NULL, '0943159715', 'YAngon', '2025-07-04 15:43:14'),
(2, 'Jhon smit', 'smit@gmail.com', NULL, 0, NULL, '0943159715', 'YAngon', '2025-07-06 03:47:40'),
(3, 'Khin Mike', 'mike@gmail.com', NULL, 0, NULL, '066663242', 'Thai', '2025-07-06 06:56:40'),
(4, 'Mya Thwin', 'fsdfsf@gmail.com', NULL, 0, NULL, '0943159715', 'YAngon', '2025-08-16 04:02:16');

-- --------------------------------------------------------

--
-- Table structure for table `customer_sessions`
--

CREATE TABLE `customer_sessions` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `session_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `description`, `image_url`, `created_at`) VALUES
(1, 'Bagan', 'Ancient city with thousands of pagodas and temples, offering hot air balloon rides and cultural experiences.', 'bagan.jpg', '2025-07-04 15:40:56'),
(2, 'Hpaan', 'Capital of Kayin State, known for limestone caves, Buddhist monasteries, and scenic mountain views.', 'Hpaan.jpg', '2025-07-04 15:40:56'),
(3, 'Taung Gyi', 'Capital of Shan State, famous for its cool climate, Inle Lake proximity, and traditional markets.', 'Taung Gyi.jpg', '2025-07-04 15:40:56'),
(4, 'Yangon', 'Former capital city, famous for Shwedagon Pagoda and colonial architecture.', 'images/yangon.jpg', '2025-08-16 01:49:36'),
(5, 'Mandalay', 'Second-largest city, known for Mandalay Hill, U Bein Bridge, and cultural heritage.', 'images/mandalay.jpg', '2025-08-16 01:49:36'),
(6, 'Bagan', 'Ancient city with thousands of pagodas and temples, UNESCO World Heritage Site.', 'images/bagan.jpg', '2025-08-16 01:49:36'),
(7, 'Inle Lake', 'A freshwater lake in Shan State, famous for floating gardens and leg-rowing fishermen.', 'images/inle_lake.jpg', '2025-08-16 01:49:36'),
(8, 'Ngapali Beach', 'A popular beach destination with white sand and clear waters on the Bay of Bengal.', 'images/ngapali.jpg', '2025-08-16 01:49:36'),
(9, 'Naypyidaw', 'The modern capital of Myanmar, featuring government buildings and wide boulevards.', 'images/naypyidaw.jpg', '2025-08-16 01:49:36'),
(10, 'Kyaiktiyo Pagoda', 'Also known as Golden Rock, a famous Buddhist pilgrimage site in Mon State.', 'images/kyaiktiyo.jpg', '2025-08-16 01:49:36'),
(11, 'Hpa-An', 'Capital of Kayin State, surrounded by limestone mountains, caves, and rivers.', 'images/hpa-an.jpg', '2025-08-16 01:49:36'),
(12, 'Mrauk U', 'Ancient city in Rakhine State, known for its stone temples and historical ruins.', 'images/mrauk_u.jpg', '2025-08-16 01:49:36'),
(13, 'Mount Popa', 'Extinct volcano and spiritual site, home to many nat shrines.', 'images/mount_popa.jpg', '2025-08-16 01:49:36'),
(14, 'Yangon', 'Former capital city, famous for Shwedagon Pagoda and colonial architecture.', 'images/yangon.jpg', '2025-08-16 01:49:36'),
(15, 'Mandalay', 'Second-largest city, known for Mandalay Hill, U Bein Bridge, and cultural heritage.', 'images/mandalay.jpg', '2025-08-16 01:49:36'),
(16, 'Bagan', 'Ancient city with thousands of pagodas and temples, UNESCO World Heritage Site.', 'images/bagan.jpg', '2025-08-16 01:49:36'),
(17, 'Inle Lake', 'A freshwater lake in Shan State, famous for floating gardens and leg-rowing fishermen.', 'images/inle_lake.jpg', '2025-08-16 01:49:36'),
(18, 'Ngapali Beach', 'A popular beach destination with white sand and clear waters on the Bay of Bengal.', 'images/ngapali.jpg', '2025-08-16 01:49:36'),
(19, 'Naypyidaw', 'The modern capital of Myanmar, featuring government buildings and wide boulevards.', 'images/naypyidaw.jpg', '2025-08-16 01:49:36'),
(20, 'Kyaiktiyo Pagoda', 'Also known as Golden Rock, a famous Buddhist pilgrimage site in Mon State.', 'images/kyaiktiyo.jpg', '2025-08-16 01:49:36'),
(21, 'Hpa-An', 'Capital of Kayin State, surrounded by limestone mountains, caves, and rivers.', 'images/hpa-an.jpg', '2025-08-16 01:49:36'),
(22, 'Mrauk U', 'Ancient city in Rakhine State, known for its stone temples and historical ruins.', 'images/mrauk_u.jpg', '2025-08-16 01:49:36'),
(23, 'Mount Popa', 'Extinct volcano and spiritual site, home to many nat shrines.', 'images/mount_popa.jpg', '2025-08-16 01:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int NOT NULL,
  `package_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `package_type` enum('Single','Double') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location_id` int DEFAULT NULL,
  `duration_days` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `includes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `approval_status` enum('Pending','Approved','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `package_name`, `package_type`, `location_id`, `duration_days`, `price`, `description`, `includes`, `image_url`, `approval_status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 'Bagan Explorer - Single', 'Single', 1, 3, 299.00, 'Explore the ancient temples of Bagan with single occupancy accommodation', 'Hotel accommodation, breakfast, guided tours, entrance fees', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/3a/a5/79/temples-de-bagan.jpg?w=1200&h=700&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(2, 'Bagan Explorer - Double', 'Double', 1, 3, 199.00, 'Explore the ancient temples of Bagan with double occupancy accommodation', 'Hotel accommodation, breakfast, guided tours, entrance fees', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/06/3a/a5/79/temples-de-bagan.jpg?w=1200&h=700&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(3, 'Hpaan Adventure - Single', 'Single', 2, 2, 249.00, 'Discover the caves and mountains of Hpaan with single occupancy', 'Hotel accommodation, all meals, cave exploration, boat trips', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/11/43/22/86/p-20171113-155557-pn.jpg?w=2400&h=-1&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(4, 'Hpaan Adventure - Double', 'Double', 2, 2, 179.00, 'Discover the caves and mountains of Hpaan with double occupancy', 'Hotel accommodation, all meals, cave exploration, boat trips', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/11/43/22/86/p-20171113-155557-pn.jpg?w=2400&h=-1&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(5, 'Taung Gyi Highland - Single', 'Single', 3, 4, 399.00, 'Experience the cool highlands of Taung Gyi with single occupancy', 'Resort accommodation, all meals, Inle Lake tour, local market visits', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0f/df/09/41/kakku-pagodas.jpg?w=1400&h=800&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(6, 'Taung Gyi Highland - Double', 'Double', 3, 4, 299.00, 'Experience the cool highlands of Taung Gyi with double occupancy', 'Resort accommodation, all meals, Inle Lake tour, local market visits', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/10/fe/24/3f/paramount-inle-resort.jpg?w=1800&h=-1&s=1', 'Approved', NULL, NULL, '2025-07-04 15:40:56'),
(7, 'Bagan VVIP', 'Double', 1, 3, 60000.00, 'AAAA', 'AAA', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/02/63/e5/8a/bullock-carts-and-pagodas.jpg?w=1400&h=800&s=1', 'Approved', NULL, NULL, '2025-07-04 16:25:35'),
(8, 'Bagan VVIP -Double', 'Double', 1, 3, 60000.00, 'AAAA', 'AAA', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/02/63/e5/8a/bullock-carts-and-pagodas.jpg?w=1400&h=800&s=1', 'Approved', NULL, NULL, '2025-07-04 16:26:01'),
(10, 'Mandalay Cultural Experience', 'Single', 5, 3, 20000000.00, 'Discover Mandalay’s monasteries, U Bein Bridge, and traditional arts. ', 'Hotel, Breakfast, Transport, Local Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP20kBwkYDilzavXScUmOkDoOhG4tT1FM6uw&s', 'Pending', NULL, NULL, '2025-08-16 03:24:23'),
(11, 'Mandalay Cultural Experience', 'Single', 5, 3, 20000000.00, 'Discover Mandalay’s monasteries, U Bein Bridge, and traditional arts. ', 'Hotel, Breakfast, Transport, Local Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP20kBwkYDilzavXScUmOkDoOhG4tT1FM6uw&s', 'Pending', NULL, NULL, '2025-08-16 03:24:42'),
(12, 'Mandalay Cultural Experience', 'Single', 5, 3, 20000000.00, 'Discover Mandalay’s monasteries, U Bein Bridge, and traditional arts. ', 'Hotel, Breakfast, Transport, Local Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP20kBwkYDilzavXScUmOkDoOhG4tT1FM6uw&s', 'Approved', 1, '2025-08-18 13:35:54', '2025-08-16 03:25:10'),
(13, 'Mandalay Cultural Experience', 'Single', 5, 3, 20000000.00, 'Discover Mandalay’s monasteries, U Bein Bridge, and traditional arts. ', 'Hotel, Breakfast, Transport, Local Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP20kBwkYDilzavXScUmOkDoOhG4tT1FM6uw&s', 'Approved', 1, '2025-08-18 13:35:48', '2025-08-16 03:25:44'),
(15, 'Mandalay Cultural Experience', 'Single', 5, 3, 20000000.00, 'Discover Mandalay’s monasteries, U Bein Bridge, and traditional arts. ', 'Hotel, Breakfast, Transport, Local Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRP20kBwkYDilzavXScUmOkDoOhG4tT1FM6uw&s', 'Pending', NULL, NULL, '2025-08-16 03:31:49'),
(16, 'Naypyidaw City Tour', 'Double', 9, 2, 120000.00, 'explore Myanmar’s modern capital with wide boulevards and museums.', 'Hotel, Breakfast, Transport, Guide', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTjVr5k_SP6xgrq11SyNV_AMzyYgOxYr1fgRA&s', 'Approved', 1, '2025-08-18 13:36:02', '2025-08-16 03:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `package_registrations`
--

CREATE TABLE `package_registrations` (
  `id` int NOT NULL,
  `customer_id` int NOT NULL,
  `package_id` int NOT NULL,
  `tour_id` int NOT NULL,
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `preferred_date` date DEFAULT NULL,
  `number_of_passengers` int DEFAULT '1',
  `special_requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `registration_status` enum('Pending','Approved','Rejected','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `processed_by` int DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `total_estimated_cost` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Pending','Partial','Paid','Refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int NOT NULL,
  `tour_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `package_id` int DEFAULT NULL,
  `bus_type_id` int DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `available_seats` int DEFAULT NULL,
  `status` enum('Active','Completed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tours`
--

INSERT INTO `tours` (`id`, `tour_name`, `package_id`, `bus_type_id`, `departure_date`, `return_date`, `available_seats`, `status`, `created_at`) VALUES
(1, 'Bagan Temple Discovery', 1, 2, '2025-08-05', '2025-08-22', 12, 'Active', '2025-07-04 15:40:56'),
(2, 'Bagan Budget Tour', 2, 1, '2025-08-20', '2025-08-23', 40, 'Active', '2025-07-04 15:40:56'),
(3, 'Hpaan Cave Explorer', 3, 2, '2025-07-25', '2025-07-27', 20, 'Active', '2025-07-04 15:40:56'),
(4, 'Hpaan Family Package', 4, 1, '2025-08-01', '2025-08-03', 35, 'Active', '2025-07-04 15:40:56'),
(5, 'Taung Gyi Premium', 5, 3, '2025-08-05', '2025-08-09', 15, 'Active', '2025-07-04 15:40:56'),
(6, 'Taung Gyi Group Tour', 6, 1, '2025-09-10', '2025-09-14', 40, 'Active', '2025-07-04 15:40:56'),
(7, 'Bagan VVIP - Single', 7, 2, '2025-07-21', '2025-07-24', 30, 'Active', '2025-07-05 07:27:27'),
(8, 'Mandalay Royal Palace Tour', 15, 2, '2025-09-05', '2025-09-24', 34, 'Active', '2025-08-16 03:40:01'),
(9, 'Naypyitaw Cultural Tour', 16, 2, '2025-09-04', '2025-09-06', 30, 'Active', '2025-08-16 03:44:17'),
(10, 'AAA', 16, 2, '2025-08-20', '2025-08-23', 34, 'Active', '2025-08-18 13:35:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `tour_id` (`tour_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `bus_types`
--
ALTER TABLE `bus_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customer_sessions`
--
ALTER TABLE `customer_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`session_token`),
  ADD KEY `idx_customer_token` (`customer_id`,`session_token`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `package_registrations`
--
ALTER TABLE `package_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `tour_id` (`tour_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_customer_status` (`customer_id`,`registration_status`),
  ADD KEY `idx_registration_date` (`registration_date`),
  ADD KEY `idx_status` (`registration_status`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `bus_type_id` (`bus_type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bus_types`
--
ALTER TABLE `bus_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_sessions`
--
ALTER TABLE `customer_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `package_registrations`
--
ALTER TABLE `package_registrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `customer_sessions`
--
ALTER TABLE `customer_sessions`
  ADD CONSTRAINT `customer_sessions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `packages_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `package_registrations`
--
ALTER TABLE `package_registrations`
  ADD CONSTRAINT `package_registrations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_registrations_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_registrations_ibfk_3` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_registrations_ibfk_4` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `tours_ibfk_2` FOREIGN KEY (`bus_type_id`) REFERENCES `bus_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
