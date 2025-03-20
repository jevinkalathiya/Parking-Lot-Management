-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2025 at 11:43 AM
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
-- Database: `parking`
--

-- --------------------------------------------------------

--
-- Table structure for table `entry_exit`
--

CREATE TABLE `entry_exit` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `mobile_no` varchar(20) NOT NULL,
  `vehicle_no` varchar(13) NOT NULL,
  `price` int(11) NOT NULL,
  `vehicle_type` varchar(700) NOT NULL,
  `entry_date_time` datetime NOT NULL,
  `exit_date_time` datetime NOT NULL,
  `parked_spot` varchar(5) NOT NULL,
  `vehicle_url_code` varchar(700) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `entry_exit`
--

INSERT INTO `entry_exit` (`id`, `owner_name`, `mobile_no`, `vehicle_no`, `price`, `vehicle_type`, `entry_date_time`, `exit_date_time`, `parked_spot`, `vehicle_url_code`) VALUES
(2, 'Jevin Kalathiya', '8523698741', 'GJ-05-JK-1234', 50, 'PQdM63DOYf2bXEZawGK4Co7RmcUutqHSipINxWVB9r8keyAvsjh0zlLT5JgF1nnOoiI2ZFxhcjmuSgdvNsaGwQzVYW17pHTqrylbLPU4fCReB9Jkt50K8A3MEX6D', '2025-03-20 15:48:43', '0000-00-00 00:00:00', '1', 'X76oLTNpF30JGS98VigadfxZOIBmUhvn4PRHecAwuKlbD2tr5YMqCszkQjy1WELHtm7zRhTDB3gCfj8rYWouiaFN1s6AXQlb9JeVn4c05MPdEUpqwvIykKO2xGZS'),
(3, 'John Doe', '9513578426', 'DD-02-GH-1234', 20, 'C5IotS2PB9hVEJdq64zGlfAYLcMaj1iKNQ3FnrvD8wWxROpb0Ugk7esHZyTmuXGylEoibwe64qu5jZBnQXHMR7DzKgPaAFYhrs9tT8WJ0dOI2V1pL3vxmkNCUScf', '2025-03-20 15:55:30', '0000-00-00 00:00:00', '6', '9IdJf6svAarXzeRpMDOqhwC2GZyx4HB0Wgb87tUY3Ej5kPiLunQFVSlmKNcTo1IhPGDYtbosEKzw7LWjMkCT02nSv8qxAeaydlFHuU95fNO4Bg3X1pVimr6JRcZQ');

-- --------------------------------------------------------

--
-- Table structure for table `parking_record`
--

CREATE TABLE `parking_record` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `mobile_no` varchar(20) NOT NULL,
  `vehicle_no` varchar(13) NOT NULL,
  `base_price` int(11) NOT NULL,
  `extra_charge` int(11) NOT NULL,
  `amt_paid` int(11) NOT NULL,
  `extra_hours` int(11) NOT NULL,
  `vehicle_type` varchar(30) NOT NULL,
  `entry_date_time` datetime NOT NULL,
  `exit_date_time` datetime NOT NULL,
  `parked_spot` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_record`
--

INSERT INTO `parking_record` (`id`, `owner_name`, `mobile_no`, `vehicle_no`, `base_price`, `extra_charge`, `amt_paid`, `extra_hours`, `vehicle_type`, `entry_date_time`, `exit_date_time`, `parked_spot`) VALUES
(1, 'Jevin', '8523698741', 'MH-04-GJ-1234', 50, 6000, 6050, 120, 'Car', '2025-02-23 20:56:22', '2025-02-28 20:56:45', '1');

-- --------------------------------------------------------

--
-- Table structure for table `parking_slot`
--

CREATE TABLE `parking_slot` (
  `id` int(11) NOT NULL,
  `slot_type` varchar(700) NOT NULL,
  `slot_start` int(50) NOT NULL,
  `slot_end` int(50) NOT NULL,
  `slot_url_code` varchar(700) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_slot`
--

INSERT INTO `parking_slot` (`id`, `slot_type`, `slot_start`, `slot_end`, `slot_url_code`) VALUES
(1, 'PQdM63DOYf2bXEZawGK4Co7RmcUutqHSipINxWVB9r8keyAvsjh0zlLT5JgF1nnOoiI2ZFxhcjmuSgdvNsaGwQzVYW17pHTqrylbLPU4fCReB9Jkt50K8A3MEX6D', 1, 5, 'aepF4PCR0hzn87xSOuKErWHdTXjJtUB1yqY6m3vkwV5Q2LMgAiZblNfDGIcso9XpvTua2Ng9AxQYjEJzG6qyFfODiV4c75nsMSPwBU3hbk8ILlRHK0tdrmWo1eZC'),
(2, 'C5IotS2PB9hVEJdq64zGlfAYLcMaj1iKNQ3FnrvD8wWxROpb0Ugk7esHZyTmuXGylEoibwe64qu5jZBnQXHMR7DzKgPaAFYhrs9tT8WJ0dOI2V1pL3vxmkNCUScf', 6, 7, 'P4zmrH15DXiv2kqLKSZOlVpUcdTnEBeboN0AWQJM38hGstywjg9auCfxIR7YF6UxF5wbz8KXoTjIMlRfLGJkA3gY7Etvny049hWCZmrHQqO1pc6dSDsVeuiaBP2N');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `encryption_key` varchar(700) NOT NULL,
  `url_code` varchar(700) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `full_name`, `email`, `password`, `encryption_key`, `url_code`) VALUES
(1, 'Jevin Kalathiya', 'test@gmail.com', 'a1BqbEUzUmM1Skwwa0pRSnM2TlIzZz09OjrGfOeuBj5qRlMVuaFKlflk', '2595ab0782d2beb48ec4109c36372f94', 'jOamzk1yTqFSJZut7UBX3KMPdg6lnxIQHN9Ao2CVD8W5piweLcv0hY4bRfsGrEl7xQhI4VN3LBGXgdzqA1ia6tHJbWjnMpSeOyZYR8wuk5s2UFKfPE9oD0rCvmTc');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_category`
--

CREATE TABLE `vehicle_category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(700) NOT NULL,
  `amount` int(11) NOT NULL,
  `cat_status` tinyint(4) NOT NULL,
  `cat_url_code` varchar(700) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_category`
--

INSERT INTO `vehicle_category` (`id`, `category_name`, `amount`, `cat_status`, `cat_url_code`) VALUES
(1, 'Car', 50, 1, 'PQdM63DOYf2bXEZawGK4Co7RmcUutqHSipINxWVB9r8keyAvsjh0zlLT5JgF1nnOoiI2ZFxhcjmuSgdvNsaGwQzVYW17pHTqrylbLPU4fCReB9Jkt50K8A3MEX6D'),
(2, 'Bike', 20, 1, 'C5IotS2PB9hVEJdq64zGlfAYLcMaj1iKNQ3FnrvD8wWxROpb0Ugk7esHZyTmuXGylEoibwe64qu5jZBnQXHMR7DzKgPaAFYhrs9tT8WJ0dOI2V1pL3vxmkNCUScf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entry_exit`
--
ALTER TABLE `entry_exit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parking_record`
--
ALTER TABLE `parking_record`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parking_slot`
--
ALTER TABLE `parking_slot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_category`
--
ALTER TABLE `vehicle_category`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entry_exit`
--
ALTER TABLE `entry_exit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `parking_record`
--
ALTER TABLE `parking_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parking_slot`
--
ALTER TABLE `parking_slot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicle_category`
--
ALTER TABLE `vehicle_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
