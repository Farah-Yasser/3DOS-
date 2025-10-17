-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 01:03 PM
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
-- Database: `jwt_api_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Blouses\r\n'),
(2, 'Pants'),
(7, 'Sets');

-- --------------------------------------------------------

--
-- Table structure for table `pass_reset`
--

CREATE TABLE `pass_reset` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `archive` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `description`, `price`, `quantity`, `image`, `category_id`, `archive`) VALUES
(1, 'New Yellow Shirt', 'Women Front Tie Long Sleeve Pleated Striped Casual Top, Everyday Wear', 350, 50, 'uploads/Yellow Shirt.jpeg', 1, 0),
(3, 'Brown Shirt', 'Women Front Tie Long Sleeve Pleated Striped Casual Top, Everyday Wear', 350, 50, 'uploads/img_68f1f5d83d5fd0.26459284.jpg', 1, 0),
(5, 'Beige Pants', 'Organic Cotton Wide Leg ', 700, 50, 'uploads/img_68f1fa9f78dbe1.36955093.jpg', 2, 0),
(6, 'Black Pants', ' Wide Leg ', 700, 50, 'uploads/img_68f1fa9f796351.27077130.jpg', 2, 0),
(11, 'Burgundy Set', 'Organic Cotton 2 piece set with solid color ', 850, 50, 'uploads/img_68f20842d194a8.11469777.jpg', 7, 0),
(12, 'Grey Set', 'Organic Cotton 2 piece set with solid color ', 1000, 50, 'uploads/img_68f20842d2ac15.53493125.jpg', 7, 0);

-- --------------------------------------------------------

--
-- Table structure for table `refresh`
--

CREATE TABLE `refresh` (
  `refresh_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` text NOT NULL,
  `expire_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refresh`
--

INSERT INTO `refresh` (`refresh_id`, `user_id`, `token`, `expire_at`, `created_at`) VALUES
(1, 4, '$2y$10$f9mVUtO7QDwFdgPfTkKteuqN6YXIe/vb2mm7Fz.bKciAFwHueJ7qq', '2025-10-20 20:13:15', '2025-10-13 21:13:15'),
(2, 4, '$2y$10$Drgvzq/iBasUKgMS5wklyuVAyzWLz6NTiQB0aD49bsILwUCDNdHLu', '2025-10-21 01:56:17', '2025-10-14 02:56:18'),
(3, 4, '$2y$10$QHo8cgVQvHHxUhkznJp4QOCmgFlvj9hs8OrUuAyrFe.EF1ycsZeam', '2025-10-21 02:04:05', '2025-10-14 03:04:05'),
(4, 4, '$2y$10$q/GUhtBVLt7sSxheGkdEWuM8EUbzw3c5jTsmdIioCuFM2LkhG8vny', '2025-10-21 15:09:36', '2025-10-14 16:09:36'),
(5, 4, '$2y$10$s/myo9em6yfB8vT2gB2saekXGFpQ1UcjtF14LjpoSIIL51xq3bK0S', '2025-10-21 16:19:59', '2025-10-14 17:19:59'),
(6, 4, '$2y$10$3Rkvnoompr82tHSYK3lLiu6ha.VWbKCUhQoyVT/WJcBnp2RTG/QSe', '2025-10-21 17:03:50', '2025-10-14 18:03:50'),
(7, 4, '$2y$10$3TOZdFxKiTw4Kxf3QQaELe6eB2MjyHhzcLkC.Lh05Xs33xbndAcje', '2025-10-21 21:33:46', '2025-10-14 22:33:46'),
(8, 4, '$2y$10$5VAL5F9yqYMVtc5bpnbsGud4IF9VAhUGitdovFwseBjOo3Aw/NUIG', '2025-10-22 02:42:35', '2025-10-15 03:42:35'),
(9, 4, '$2y$10$Dum/jISRAHqPw2.fbVYz6OxZYR0ANeKNDehDMikyY.yJZY1Rv9Jd.', '2025-10-22 03:07:18', '2025-10-15 04:07:18'),
(10, 5, '$2y$10$SrCWi.Os7QQojte2pcFPEOvW0rBuZLb5xjlCgurA08PrXL/tdu3/6', '2025-10-22 03:21:05', '2025-10-15 04:21:05'),
(11, 4, '$2y$10$hmaDx6yBG0pESTm9Ttduo.inPL2jUpBuU8c/9dsOblis0wqDYIsKG', '2025-10-22 13:53:56', '2025-10-15 14:53:56'),
(12, 4, '$2y$10$PUb0plQT4ZVBxHxGzZGDbuhJf.BelwvqBe3krsYYhSjjxK5NQ2rQq', '2025-10-23 01:18:15', '2025-10-16 02:18:15'),
(13, 4, '$2y$10$dWkxfcGlcd3VhJiJIOB9wu4M5y4FeUB.beDMInSghojyaKeMefA.u', '2025-10-23 03:22:46', '2025-10-16 04:22:46'),
(14, 4, '$2y$10$7ziSW7Ce6/SuP7eZUCWP8OGk.4.qozR5Dlf2DFzRtE80XSXM76V6O', '2025-10-23 03:30:22', '2025-10-16 04:30:22'),
(15, 4, '$2y$10$QaRL/FwHS5fsw09kN4hyZeimXdjvjBJOnCxljtutqx1VuRuGx7mXu', '2025-10-23 03:32:07', '2025-10-16 04:32:07'),
(16, 4, '$2y$10$bkhVIjx7lYVy0UiGJMb11eFv/IpAHuGKZuNadO5GN8CHtLu7rDlQG', '2025-10-23 03:32:11', '2025-10-16 04:32:11'),
(17, 4, '$2y$10$8tbUtglyLFeXrvnhSyu4yeEq9BxJeW7zcR67LFCHcBB8qp/7yEd1S', '2025-10-23 03:32:39', '2025-10-16 04:32:39'),
(18, 4, '$2y$10$OOOs/TJmUOadRSP4agV9zeENyzCo9bt8LiNIslgXnRZ/E3X5fM9u6', '2025-10-23 01:42:01', '2025-10-16 04:42:01'),
(19, 4, '$2y$10$o/88rxzQ177ik6RFPI/mDexkgQvxzYEDCDLn7k4MqQuxE6AktDFW6', '2025-10-23 12:22:24', '2025-10-16 15:22:24'),
(20, 4, '$2y$10$lf.87Vi5FaQ89HbWaMaGo.AcntVHv33JpOjVdeI3/buUxEhK7kck6', '2025-10-23 14:51:33', '2025-10-16 17:51:33'),
(21, 4, '$2y$10$RGbxES1sayMYBkfCc5b6/O97C2Wud0izfyNO5VV8Ew0GMPAuegiUi', '2025-10-23 16:28:20', '2025-10-16 19:28:20'),
(22, 4, '$2y$10$URvtT/UYDnvzDoa2/Tjrgueq/.z9niA5NY3NyJkwK3Nfue2QbdrWq', '2025-10-23 16:36:40', '2025-10-16 19:36:40'),
(23, 4, '$2y$10$E7RWvpr5IsKYuvNvi2EmNONadQz9j9tWOIQRcRWYW4B14cpIfpry6', '2025-10-23 17:39:43', '2025-10-16 20:39:43'),
(24, 4, '$2y$10$N0BDumi9.jaztELgUsYbEuQBlai.JxIvJWHqRxWTze3BcOXmtdKWC', '2025-10-23 18:10:06', '2025-10-16 21:10:06'),
(25, 4, '$2y$10$RTf4/j75ev3t.2mIJ.cg3uZpXZYukV5vXkkEMbVKi4AvqWXGLDyqC', '2025-10-23 18:17:43', '2025-10-16 21:17:43'),
(26, 4, '$2y$10$9ZcVxgz7vZTaolRnl7npieROT6Ecrpr8bZFnoTvtq00e/7kz23luu', '2025-10-23 20:16:38', '2025-10-16 23:16:38'),
(27, 4, '$2y$10$b/Sxq8Mzjf0QZLVHz8xfnO3aaqTM/SPcrj77NeCKNnoBs/umH9PIm', '2025-10-24 00:46:30', '2025-10-17 03:46:30'),
(28, 4, '$2y$10$QbKqwVH4HlEB3zbELX91Ju8DhCWKDy1/xj5VZhwQACe6.cHmjCZ3e', '2025-10-24 07:26:16', '2025-10-17 10:26:16'),
(29, 4, '$2y$10$kHgpPTQI5pmGDz8ePR3cRO/KceOaUXxgYS8SnxVFo715nqhStNoxK', '2025-10-24 09:03:29', '2025-10-17 12:03:29');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role`) VALUES
(1, 'admin'),
(2, 'user'),
(3, 'editor');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `created_at`, `role_id`) VALUES
(4, 'farah', 'farahyasser04@gmail.com', '$2y$10$5ny7GIF68HMR9I6RB/ymu.EDgb9oe2lZS2oJAKfJ0e5avgRjf8q3e', '2025-10-13 00:27:51', 1),
(5, 'sarah', 'sarah@gmail.com', '$2y$10$VJQvnPrAop99XvovjTdX1.ApUIE21gZMwxDFpYOiU/R8qBbkmLeS.', '2025-10-13 00:28:18', 3),
(6, 'malak', 'malak@gmail.com', '$2y$10$wDVs3HIyzP74SFPEGl8ygeaKKUAvjvWDA84zMsV9ftWqQ5xYzFZj2', '2025-10-13 00:31:35', 2),
(7, 'eyad', 'eyad@gmail.com', '$2y$10$TNvocJjssS.YCsLx4gn/S.R5R4HmKS5zmyXT6cTCKSuEpO/Zi7bly', '2025-10-13 00:31:50', 2),
(11, 'farahy', 'farah@gmail.com', '$2y$10$P98rwAxi/ivaXfB6dveRbuU3FrfyYLmrXbCH9VsqUBFL8phuEMCwS', '2025-10-14 02:58:08', 1),
(12, 'farahyass', 'farah@gmail.com', '$2y$10$daxkiuSsGelYt.kyV4kPee77lM1K6melxpTZJNiB6s78zbiJQg9M.', '2025-10-14 02:59:22', 1),
(14, 'eyadd', 'eyadd@gmail.com', '$2y$10$ypUR7o5MSAUNxq94jvUQP.0ZHQyDc.qaFiR6BFhqA7esOBlFInkRu', '2025-10-14 22:22:12', 1),
(15, 'farahhh', 'farahyasser04@gmail.com', '$2y$10$7z63H6Vw.3tDfzmbpu5leOfCVp4Q9mOEKTwLGlc3tBXkR/UQrNdKa', '2025-10-17 11:42:49', 3),
(16, 'farahhhh', 'farahyasser04@gmail.com', '$2y$10$1BNijFYOJMYHCNqY3.v1.OJm0leBKlm8CWNRzXo.ch6mGFMdBIP12', '2025-10-17 11:45:05', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `pass_reset`
--
ALTER TABLE `pass_reset`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `refresh`
--
ALTER TABLE `refresh`
  ADD PRIMARY KEY (`refresh_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pass_reset`
--
ALTER TABLE `pass_reset`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `refresh`
--
ALTER TABLE `refresh`
  MODIFY `refresh_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pass_reset`
--
ALTER TABLE `pass_reset`
  ADD CONSTRAINT `pass_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `refresh`
--
ALTER TABLE `refresh`
  ADD CONSTRAINT `refresh_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
