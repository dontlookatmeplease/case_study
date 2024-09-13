-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Sep 08, 2024 at 05:58 PM
-- Server version: 9.0.1
-- PHP Version: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `case_study_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cs_product_list`
--

CREATE TABLE `cs_product_list` (
  `pl_id` int NOT NULL,
  `pl_title` varchar(255) NOT NULL,
  `pl_body` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cs_product_list`
--

INSERT INTO `cs_product_list` (`pl_id`, `pl_title`, `pl_body`, `date_created`) VALUES
(1, 'testing_product_list', '[{}]', '2024-09-08 17:52:47'),
(2, 'testing_product_list_second', '[{}]', '2024-09-08 17:53:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cs_product_list`
--
ALTER TABLE `cs_product_list`
  ADD PRIMARY KEY (`pl_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cs_product_list`
--
ALTER TABLE `cs_product_list`
  MODIFY `pl_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
