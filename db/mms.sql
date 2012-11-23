-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2012 at 08:05 PM
-- Server version: 5.1.53
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mms`
--

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE IF NOT EXISTS `order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(24) NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(2000) DEFAULT NULL,
  `state` int(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `userId`, `amount`, `created`, `comment`, `state`) VALUES
(1, '50af7aa0f12a6f7c08000000', '25.00', '2012-11-23 13:36:10', 'test1', 0),
(2, '50af7a71f12a6f740a000001', '10.00', '2012-11-23 13:36:10', 'test2', 0),
(3, '50af7aa0f12a6f7c08000000', '17.00', '2012-11-23 13:36:31', 'test3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE IF NOT EXISTS `order_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `itemId` varchar(24) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`id`, `orderId`, `itemId`, `price`, `count`) VALUES
(1, 1, '50af7b17f12a6f7c08000001', '1.00', 10),
(2, 1, '50af7b03f12a6f740a000002', '1.00', 15),
(3, 2, '50af7b17f12a6f7c08000001', '1.00', 4),
(4, 2, '50af7b03f12a6f740a000002', '1.00', 6),
(5, 3, '50af7b17f12a6f7c08000001', '1.00', 9),
(6, 3, '50af7b03f12a6f740a000002', '1.00', 8);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
