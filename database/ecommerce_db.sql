-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 03:03 AM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `customer_note` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_image` varchar(255) DEFAULT 'default.jpg',
  `status` enum('Pending','Processing','Shipped','Delivered') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `product_name`, `description`, `price`, `customer_name`, `contact`, `address`, `customer_note`, `order_date`, `product_image`, `status`) VALUES
(45, 'ROG Zephyrus S17', 'thanks', 1200.00, 'SENGHOK', 'yongfut@gmail.com / 0716957300', NULL, NULL, '2025-12-26 21:34:15', '1766676265_633adbc8d9f918464775a999-asus-rog-strix-scar-15-2022-gaming.jpg', 'Pending'),
(46, 'Aula S75 Pro', 'thanks', 60.00, 'SENGHOK', 'yongfut@gmail.com / 0716957300', NULL, NULL, '2025-12-26 21:34:15', '1766714744_104.jpg', 'Pending'),
(47, 'labogini ', 'thanks', 1000000.00, 'TREK', '0968329409', NULL, NULL, '2025-12-26 22:42:08', '1766724368_images.jpg', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `in_stock` tinyint(1) DEFAULT 1,
  `is_discount` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `in_stock`, `is_discount`) VALUES
(5, 'Mustang', 34000.00, 'car color black', '1766587905_tyler-clemmensen-Zs_L-plsZzg-unsplash.jpg', 1, 0),
(6, 'ROG Zephyrus S17', 1200.00, 'OG Zephyrus S17 is a powerful, slim 17-inch gaming laptop from ASUS, known for blending high-end performance (up to RTX 3080/Core i9) with innovative features like an optical-mechanical keyboard that lifts for better cooling and an innovative GPU switch (Advanced Optimus) for dynamic performance/battery life, all packed into a relatively sleek chassis with fast display options (165Hz/4K) and robust cooling for serious gamers and creators. ', '1766676265_633adbc8d9f918464775a999-asus-rog-strix-scar-15-2022-gaming.jpg', 1, 1),
(7, 'Akko k85', 43.00, 'have only color in stock', '1766593028_586866186_801951482841234_7304837383050070490_n.jpg', 1, 0),
(10, 'HIBI Office Beige Keycaps', 25.00, 'HIBI Office Beige Keycaps are minimal keycaps with a beige base and colorful, black legends, and multi-colored sublegends for a simple yet playful keycap set.', '1766710055_76500db4-42c2-467a-9414-aab61dcaf921.png', 1, 0),
(11, 'Gravastar mercury k1 pro yellow', 179.00, 'limitedition', '1766713015_k1proyellow.jpg', 1, 0),
(12, 'Aula S75 Pro', 60.00, 'Aula S75 Pro Wireless + Bluetooth HotSwap Mechanical RGB Gaming Keyboard (Black)\r\n', '1766714744_104.jpg', 0, 0),
(13, 'labogini ', 1000000.00, 'have only', '1766724368_images.jpg', 0, 1),
(14, 'Kailh Mechanical Key Switches', 11.99, 'For crafting your very own custom keyboard, these Kailh Red Linear mechanical key switches are deeee-luxe! With smooth actuation and Cherry MX compatibility, they\'re lovely when you want a smooth linear keystroke:\r\n\r\nType Linear\r\nOperating force: 45 gf (+/-10 gf)\r\nPretravel: 1.8 mm (+/- 0.3 mm)\r\nTotal travel: 3.6 mm (+/- 0.3 mm)\r\nReset point: 1.8 mm', '1766809383_4952-00.jpg', 1, 0),
(15, 'GATERON Magnetic Emperor Switches', 22.00, 'for Magnetic Gaming Keyboard, Linear/20mm Longer Spring/Pre-lubed/Freely Setting Pre-Travel Key Switches(108pcs)', '1766810172_81u8SPqnVBL.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@test.com', '123123', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
