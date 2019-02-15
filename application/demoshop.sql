-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2019 at 03:24 PM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.1.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demoshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `attribute`
--

CREATE TABLE `attribute` (
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute`
--

INSERT INTO `attribute` (`attribute_id`, `attribute_group_id`, `sort_order`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(5, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `attribute_group`
--

CREATE TABLE `attribute_group` (
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute_group`
--

INSERT INTO `attribute_group` (`attribute_group_id`, `sort_order`) VALUES
(1, 1),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `attribute_group_language`
--

CREATE TABLE `attribute_group_language` (
  `attribute_group_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute_group_language`
--

INSERT INTO `attribute_group_language` (`attribute_group_id`, `language_id`, `name`) VALUES
(1, 1, 'مشخصات فیزیکی'),
(2, 1, 'نمایشگر'),
(3, 1, 'گرافیک'),
(1, 2, 'Physical Information'),
(2, 2, 'Display'),
(3, 2, 'VGA Card');

-- --------------------------------------------------------

--
-- Table structure for table `attribute_language`
--

CREATE TABLE `attribute_language` (
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute_language`
--

INSERT INTO `attribute_language` (`attribute_id`, `language_id`, `name`) VALUES
(1, 1, 'ابعاد'),
(1, 2, 'Dimension'),
(2, 1, 'وزن'),
(2, 2, 'Weight'),
(3, 1, 'تعداد سیم'),
(3, 2, 'Sim Cell Count'),
(5, 1, 'رزولیشن'),
(5, 2, 'Resulation');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL,
  `top` int(10) UNSIGNED NOT NULL,
  `level` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `status` int(10) UNSIGNED NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL,
  `date_updated` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `parent_id`, `top`, `level`, `sort_order`, `status`, `date_added`, `date_updated`) VALUES
(2, 0, 0, 0, 1, 1, 1550077940, 1550077940),
(5, 0, 0, 0, 4, 1, 1550127391, 1550127391),
(6, 2, 0, 1, 5, 1, 1550127952, 1550127952),
(7, 2, 0, 1, 6, 1, 1550213564, 1550213564);

-- --------------------------------------------------------

--
-- Table structure for table `category_filter`
--

CREATE TABLE `category_filter` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `filter_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category_filter`
--

INSERT INTO `category_filter` (`category_id`, `filter_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `category_language`
--

CREATE TABLE `category_language` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category_language`
--

INSERT INTO `category_language` (`category_id`, `language_id`, `name`) VALUES
(2, 1, 'کالای دیجیتال'),
(2, 2, 'Digital Product'),
(5, 1, 'مردانه'),
(5, 2, 'Men'),
(6, 1, 'موبایل'),
(6, 2, 'Mobile'),
(7, 1, 'کامپیوتر'),
(7, 2, 'Computer');

-- --------------------------------------------------------

--
-- Table structure for table `category_path`
--

CREATE TABLE `category_path` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `path_id` int(10) UNSIGNED NOT NULL,
  `level` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `category_path`
--

INSERT INTO `category_path` (`category_id`, `path_id`, `level`) VALUES
(2, 2, 0),
(5, 5, 0),
(6, 2, 0),
(6, 6, 1),
(7, 2, 0),
(7, 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `config_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `key` varchar(45) NOT NULL,
  `value` text NOT NULL,
  `serialized` int(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`config_id`, `code`, `name`, `key`, `value`, `serialized`) VALUES
(1, 'info_config', 'عنوان فروشگاه', 'shop_title', 'فروشگاه من', 0),
(2, 'info_config', 'توضیح کوتاه فروشگاه', 'short_description', 'این بهترین فروشگاه است', 0),
(3, 'security_config', 'حداکثر زمان غیر فعال بودن سشن لاگین کاربر', 'max_inactive_login_session_time', '1200', 0),
(4, 'security_config', 'حداکثر زمان توکن لاگین کاربر', 'max_token_time_expiry', '1200', 0),
(5, 'manufacturer_info', 'عکس پیش فرض تولیدکننده', 'manufacturer_image_default', 'http://myshopdemo.test/assets/img/no-image.jpeg', 0),
(6, 'option_info', 'انواع گزینه های محصول', 'option_type', 'a:4:{i:0;s:6:\"select\";i:1;s:8:\"checkbox\";i:2;s:5:\"radio\";i:3;s:5:\"color\";}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `filter`
--

CREATE TABLE `filter` (
  `filter_id` int(10) UNSIGNED NOT NULL,
  `filter_group_id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `filter`
--

INSERT INTO `filter` (`filter_id`, `filter_group_id`, `sort_order`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(13, 3, 1),
(14, 3, 2),
(15, 3, 3),
(16, 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `filter_group`
--

CREATE TABLE `filter_group` (
  `filter_group_id` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `filter_group`
--

INSERT INTO `filter_group` (`filter_group_id`, `sort_order`) VALUES
(1, 1),
(3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `filter_group_langauge`
--

CREATE TABLE `filter_group_langauge` (
  `filter_group_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `filter_group_langauge`
--

INSERT INTO `filter_group_langauge` (`filter_group_id`, `language_id`, `name`) VALUES
(1, 1, 'رم'),
(1, 2, 'Ram'),
(3, 1, 'CPU'),
(3, 2, 'CPU');

-- --------------------------------------------------------

--
-- Table structure for table `filter_language`
--

CREATE TABLE `filter_language` (
  `filter_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `filter_language`
--

INSERT INTO `filter_language` (`filter_id`, `language_id`, `name`) VALUES
(1, 1, '2GB'),
(2, 1, '4GB'),
(3, 1, '8GB'),
(4, 1, '12GB'),
(5, 1, '16GB'),
(13, 1, 'Core i3'),
(14, 1, 'Core i5'),
(15, 1, 'Core i7'),
(16, 1, 'Core i9');

-- --------------------------------------------------------

--
-- Table structure for table `langauge`
--

CREATE TABLE `langauge` (
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(3) NOT NULL,
  `name` varchar(40) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `langauge`
--

INSERT INTO `langauge` (`language_id`, `code`, `name`, `image`, `sort_order`, `status`) VALUES
(1, 'fa', 'فارسی', '', 1, 1),
(2, 'en', 'English', '', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `length`
--

CREATE TABLE `length` (
  `length_id` int(10) UNSIGNED NOT NULL,
  `value` decimal(15,8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `length_language`
--

CREATE TABLE `length_language` (
  `length_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `unit` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturer`
--

CREATE TABLE `manufacturer` (
  `manufacturer_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `url` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `manufacturer`
--

INSERT INTO `manufacturer` (`manufacturer_id`, `image`, `sort_order`, `status`, `url`) VALUES
(2, 'http://myshopdemo.test/assets/img/manufacturer/1080.png', 1, 1, 'pars-khazar'),
(3, 'http://myshopdemo.test/assets/img/manufacturer/2315.png', 2, 1, 'x-vision'),
(7, 'http://myshopdemo.test/assets/img/manufacturer/logo.png', 3, 1, 'glx'),
(8, 'http://myshopdemo.test/assets/img/no-image.jpeg', 4, 1, 'samsung'),
(9, 'http://myshopdemo.test/assets/img/no-image.jpeg', 5, 1, 'apple');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturer_language`
--

CREATE TABLE `manufacturer_language` (
  `manufacturer_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `manufacturer_language`
--

INSERT INTO `manufacturer_language` (`manufacturer_id`, `language_id`, `name`) VALUES
(2, 1, 'پارس  خزر'),
(3, 1, 'ایکس ویژن'),
(3, 2, 'X Vision'),
(7, 1, 'جی ال ایکس'),
(7, 2, 'GLX'),
(8, 1, 'سامسونگ'),
(8, 2, 'Samsung'),
(9, 1, 'اپل'),
(9, 2, 'Apple');

-- --------------------------------------------------------

--
-- Table structure for table `option`
--

CREATE TABLE `option` (
  `option_id` int(10) UNSIGNED NOT NULL,
  `option_type` varchar(40) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `option`
--

INSERT INTO `option` (`option_id`, `option_type`, `sort_order`) VALUES
(6, 'checkbox', 1);

-- --------------------------------------------------------

--
-- Table structure for table `option_language`
--

CREATE TABLE `option_language` (
  `option_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `option_language`
--

INSERT INTO `option_language` (`option_id`, `language_id`, `name`) VALUES
(1, 1, 'رنگ محصول'),
(1, 2, 'Product Color'),
(2, 1, 'رنگ محصول'),
(2, 2, 'Product Color'),
(3, 1, 'fdgfdg'),
(3, 2, 'fdgdfg'),
(4, 1, 'alaki'),
(4, 2, 'dsffsdf'),
(5, 1, 'dsfdsf'),
(5, 2, 'dsfdsfd'),
(6, 1, 'رنگ محصول'),
(6, 2, 'Product Color'),
(7, 1, 'سلام'),
(7, 2, 'Hello');

-- --------------------------------------------------------

--
-- Table structure for table `option_value`
--

CREATE TABLE `option_value` (
  `option_value_id` int(10) UNSIGNED NOT NULL,
  `option_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `option_value`
--

INSERT INTO `option_value` (`option_value_id`, `option_id`, `image`, `sort_order`) VALUES
(6, 4, '', 1),
(7, 4, '', 2),
(8, 3, '', 1),
(9, 3, '', 2),
(10, 5, '', 1),
(11, 5, '', 2),
(12, 6, 'http://myshopdemo.test/assets/img/option/white.jpg', 1),
(13, 6, 'http://myshopdemo.test/assets/img/option/black.jpg', 2),
(14, 6, 'http://myshopdemo.test/assets/img/option/blue.jpg', 3),
(15, 6, 'http://myshopdemo.test/assets/img/option/red.jpg', 4),
(16, 6, 'http://myshopdemo.test/assets/img/option/yellow.jpg', 5),
(24, 7, 'http://myshopdemo.test/assets/img/option/blue.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `option_value_language`
--

CREATE TABLE `option_value_language` (
  `option_value_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `option_value_language`
--

INSERT INTO `option_value_language` (`option_value_id`, `language_id`, `name`) VALUES
(1, 1, 'سفید'),
(1, 2, 'White'),
(2, 1, 'سیاه'),
(2, 2, 'Black'),
(3, 1, 'آبی'),
(3, 2, 'Blue'),
(4, 1, 'قرمز'),
(4, 2, 'Red'),
(5, 1, 'زرد'),
(5, 2, 'Yellow'),
(6, 1, 'gdfg'),
(6, 2, 'fsdfd'),
(7, 1, 'gdfgdf'),
(7, 2, 'dsfdsf'),
(8, 1, 'hjghjh'),
(8, 2, 'gfdg'),
(9, 1, 'kjhhkhj'),
(9, 2, 'hjhgjhj'),
(10, 1, 'hjkjhk'),
(10, 2, 'dsfds'),
(11, 1, 'khjkjh'),
(11, 2, 'ljhlhl'),
(12, 1, 'سفید'),
(12, 2, 'White'),
(13, 1, 'سیاه'),
(13, 2, 'Black'),
(14, 1, 'آبی'),
(14, 2, 'Blue'),
(15, 1, 'قرمز'),
(15, 2, 'Red'),
(16, 1, 'زرد'),
(16, 2, 'Yellow'),
(17, 1, 'سلام'),
(17, 2, 'Hello'),
(18, 1, 'س'),
(18, 2, 'Hi'),
(19, 1, 'سلام'),
(19, 2, 'Hello'),
(20, 1, 'س'),
(20, 2, 'Hi'),
(21, 1, 'سلام'),
(21, 2, 'Hello'),
(22, 1, 'س'),
(22, 2, 'Hi'),
(23, 1, 'سلام'),
(23, 2, 'Hello'),
(24, 1, 'سلام'),
(24, 2, 'Hello');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `stock_status_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `manufacturer_id` int(10) UNSIGNED NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `date_available` int(10) UNSIGNED NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL,
  `date_updated` int(10) UNSIGNED NOT NULL,
  `weight` decimal(15,4) NOT NULL,
  `weight_id` tinyint(3) UNSIGNED NOT NULL,
  `height` decimal(15,4) NOT NULL,
  `width` decimal(15,4) NOT NULL,
  `length` decimal(15,4) NOT NULL,
  `length_id` tinyint(3) UNSIGNED NOT NULL,
  `minimum` int(10) UNSIGNED NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `viewed` int(10) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_attribute`
--

CREATE TABLE `product_attribute` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_discount`
--

CREATE TABLE `product_discount` (
  `product_discount_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL,
  `date_start` int(10) UNSIGNED NOT NULL,
  `date_end` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_filter`
--

CREATE TABLE `product_filter` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `filter_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_image`
--

CREATE TABLE `product_image` (
  `product_image_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_language`
--

CREATE TABLE `product_language` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `meta_title` varchar(150) NOT NULL,
  `meta_description` text NOT NULL,
  `meta_keyword` text NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_option`
--

CREATE TABLE `product_option` (
  `product_option_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `option_id` int(10) UNSIGNED NOT NULL,
  `value` varchar(60) NOT NULL,
  `required` tinyint(1) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_option_value`
--

CREATE TABLE `product_option_value` (
  `product_option_value_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_option_id` int(10) UNSIGNED NOT NULL,
  `option_id` int(10) UNSIGNED NOT NULL,
  `option_value_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `subtract` tinyint(3) UNSIGNED NOT NULL,
  `price_prefix` varchar(10) NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `weight_prefix` varchar(10) NOT NULL,
  `weight` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_related`
--

CREATE TABLE `product_related` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `related_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_special`
--

CREATE TABLE `product_special` (
  `product_special_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `price` int(10) UNSIGNED NOT NULL,
  `priority` int(10) UNSIGNED NOT NULL,
  `date_start` int(10) UNSIGNED NOT NULL,
  `date_end` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `session_id` varchar(65) NOT NULL,
  `data` text NOT NULL,
  `expiry` int(10) UNSIGNED NOT NULL,
  `modify` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `data`, `expiry`, `modify`) VALUES
('05o5uh8o8hfq3c880l80hf8a38', '\"token|s:24:\\\"5eb7aa18456381645c3da6bf\\\";token_time_expiry|i:1549774157;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549772926;login_time_expiry|i:1549774157;login_status|i:1;\"', 1549774401, 1549772961),
('0e54ct4avvqslu8q0g2qgo4lc9', '\"token|s:24:\\\"2cac44b125e9d3f627b17220\\\";token_time_expiry|i:1550127854;user|a:0:{}\"', 1550128095, 1550126655),
('0u74ajtu9j6hqe7rmcdbp2bom7', '\"token|s:24:\\\"b6064508822dad01a23b78d4\\\";token_time_expiry|i:1550058812;user|a:0:{}\"', 1550059052, 1550057612),
('1rlfrpjc95lu45si4msjom4id3', '\"user|a:0:{}\"', 1549825376, 1549823936),
('20o8p801v7hsm2mad2kkce5179', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"ebf282cb346fd01891eddf90\\\";token_time_expiry|i:1549646068;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549640559;login_time_expiry|i:1549646068;login_status|i:1;\"', 1549646308, 1549644868),
('240t91uj9obifjdo3ovvb344d7', '\"user|a:0:{}\"', 1549787249, 1549785809),
('2b2kddonc9trnkv1iavrphsevm', '\"token|s:24:\\\"e6cdbc93583be2c3d295f475\\\";token_time_expiry|i:1550147459;user|a:0:{}\"', 1550147700, 1550146260),
('2k0ogqqs379830de8jp4uteebj', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"e79edb06c26a56929d91de3b\\\";token_time_expiry|i:1549634214;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549629847;login_time_expiry|i:1549634214;login_status|i:1;\"', 1549634461, 1549633021),
('362v35mqr9g4qmpg856pgdldn2', '\"user|a:0:{}\"', 1549801736, 1549800296),
('4ra384a9bm3h0ro2luftvrrsnu', '\"token|s:24:\\\"fb9e3b6d3357834e1080c8a0\\\";token_time_expiry|i:1549774124;user|a:0:{}\"', 1549774366, 1549772926),
('4s6jgrfus2md4d7pabbs4g4qjv', '\"user|a:0:{}\"', 1549776831, 1549775391),
('622onfvaqd9namvom8f4so1m14', '\"user|a:0:{}\"', 1549958539, 1549957099),
('6r68k223rmf79ind84bifkl670', '\"token|s:24:\\\"7ff04f1816addcd50125fe45\\\";token_time_expiry|i:1549905190;user|a:0:{}\"', 1549905431, 1549903991),
('7ncp7fbq5u7glbmr3c4rsllpo4', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"ea06e6373c0019f079beca95\\\";token_time_expiry|i:1549778924;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549775391;login_time_expiry|i:1549778924;login_status|i:1;\"', 1549779165, 1549777725),
('7ska1m54r7bh01kmoermuep07k', '\"token|s:24:\\\"de70fdc3582f63204bb3b563\\\";token_time_expiry|i:1550065048;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550057612;login_time_expiry|i:1550065048;login_status|i:1;\"', 1550065288, 1550063848),
('8ev8gl1qtujqbnjvl6tjua0nfv', '\"user|a:0:{}\"', 1549953248, 1549951808),
('8gpg1k54ekpqc8033u3c54dfe9', '\"token|s:24:\\\"3f3bcb8b8b37309cb7a4513b\\\";token_time_expiry|i:1549901766;user|a:0:{}\"', 1549902006, 1549900566),
('92rlg2saiq77d4n40280tlgftc', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"2a83da7c285cd2d09239bdc9\\\";token_time_expiry|i:1549789438;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549787772;login_time_expiry|i:1549789438;login_status|i:1;\"', 1549789679, 1549788239),
('9g5l099fem2nahiiafnogn327f', '\"token|s:24:\\\"02970b1ec6ef496cef129a53\\\";token_time_expiry|i:1549972400;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549971200;login_time_expiry|i:1549972400;login_status|i:1;\"', 1549972640, 1549971200),
('a77jf8k7g1ksv16vol8464558n', '\"user|a:0:{}\"', 1549982504, 1549981064),
('camglmm5588if2inia7u91lc85', '\"user|a:0:{}\"', 1549910701, 1549909261),
('cmlmvq55sevrls6p4raeuslpb4', '\"token|s:24:\\\"d68a0a065cc8d202b05b56c9\\\";token_time_expiry|i:1550233852;user|a:0:{}\"', 1550234092, 1550232652),
('cq0bgl3r5pqvlrl9bjh67aurqe', '\"user|a:0:{}\"', 1550076077, 1550074637),
('dmdt4a6s6su8r2ecbrfj8eskf7', '\"token|s:24:\\\"a97d316a2d215b05a11c69e2\\\";token_time_expiry|i:1550073583;user|a:0:{}\"', 1550073823, 1550072383),
('dmmgjc2hf5hhiguq55eglh3dll', '\"token|s:24:\\\"8cba882b61293ba2552be006\\\";token_time_expiry|i:1549782889;user|a:0:{}\"', 1549783129, 1549781689),
('efbuca0o3acj32qb25fkj1ajcb', '\"token|s:24:\\\"fdefd75b6a59cc73774f3350\\\";token_time_expiry|i:1549903347;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549900567;login_time_expiry|i:1549903347;login_status|i:1;\"', 1549903592, 1549902152),
('eoqhrlb3589savecnvgtl3a310', '\"user|a:0:{}\"', 1550078303, 1550076863),
('f7opvr2cf5fobfkrldeqq2mpua', '\"token|s:24:\\\"41a28bb3a7cf883b3b14c953\\\";token_time_expiry|i:1549949226;user|a:0:{}\"', 1549949466, 1549948026),
('fduah08ou2cepdtdma4jmth643', '\"token|s:24:\\\"dc32142b6e73193adb4af946\\\";token_time_expiry|i:1549978454;user|a:0:{}\"', 1549978694, 1549977254),
('fead5ad8ff7rbq5umic1bq5q9d', '\"token|s:24:\\\"8e5fbe45831fff560bece7b6\\\";token_time_expiry|i:1550215271;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550211017;login_time_expiry|i:1550215271;login_status|i:1;\"', 1550215511, 1550214071),
('fei5mft7i446oblrpjovsv1cbu', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"853dd664d22e787022257650\\\";token_time_expiry|i:1549630029;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549625571;login_time_expiry|i:1549630029;login_status|i:1;\"', 1549630269, 1549628829),
('fs7o8bm04hn28e1jon63a5rn03', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"d9b753520487f79cd7f614e2\\\";token_time_expiry|i:1549827012;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549823936;login_time_expiry|i:1549827012;login_status|i:1;\"', 1549827253, 1549825813),
('fu6lbfisd0en2f2efbfoltond3', '\"user|a:0:{}\"', 1550149965, 1550148525),
('h6fqkaluv5c22dtctplm0a33k9', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"ad2a227c67e7686fa22fd20e\\\";token_time_expiry|i:1549913085;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549909261;login_time_expiry|i:1549913085;login_status|i:1;\"', 1549913380, 1549911940),
('h7q25vtpct0g5ovnrbmm51hrfr', '\"token|s:24:\\\"23c0ac9dfe283b422b1bdb1c\\\";token_time_expiry|i:1550212217;user|a:0:{}\"', 1550212457, 1550211017),
('i1qgti9nqbqquhbvr416fukqhq', '\"token|s:24:\\\"e5e36aff881ce8154d50531b\\\";token_time_expiry|i:1549972456;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549971209;login_time_expiry|i:1549972456;login_status|i:1;\"', 1549972699, 1549971259),
('i83ji33jgdbmt9alk7hb53q5f0', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"c084f9bd6ce8c8d545e28c09\\\";token_time_expiry|i:1549975464;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549974081;login_time_expiry|i:1549975464;login_status|i:1;\"', 1549975704, 1549974264),
('kf9bj0rf1k3vdgp91mino9akqf', '\"token|s:24:\\\"bb75527e358e430135795992\\\";token_time_expiry|i:1550233920;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550232652;login_time_expiry|i:1550233920;login_status|i:1;\"', 1550234161, 1550232721),
('kil8m7udghfm48csbobkmv203v', '\"user|a:0:{}\"', 1549631287, 1549629847),
('krl8v92l3s8illjses2lrcmnpb', '\"user|a:0:{}\"', 1550154632, 1550153192),
('lb4vutghm991ergegr1g4u9arn', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"8b7f05885d177148922dd31b\\\";token_time_expiry|i:1550156279;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550153192;login_time_expiry|i:1550156279;login_status|i:1;\"', 1550156520, 1550155080),
('lghhqochdfmmueh7hgc2h178j4', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"9e338a1ed72fe8d4f386e67d\\\";token_time_expiry|i:1549958507;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549957099;login_time_expiry|i:1549958507;login_status|i:1;\"', 1549958752, 1549957312),
('nvmqp2a7mvr3oubpninrp9t9h1', '\"user|a:0:{}\"', 1549774794, 1549773354),
('oucg79s4asfbl1e18khqjk2or1', '\"token|s:24:\\\"db399ecba987573f84b3077f\\\";token_time_expiry|i:1549972399;user|a:0:{}\"', 1549972696, 1549971256),
('p9vj2tpv8k0on1svfp65v0o7rn', '\"user|a:0:{}\"', 1549907980, 1549906540),
('puju5udbf9ltkle8bq9ddqbsim', '\"token|s:24:\\\"2962c1e11879add641e4f43a\\\";token_time_expiry|i:1550130842;user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550126655;login_time_expiry|i:1550130842;login_status|i:1;\"', 1550131084, 1550129644),
('q9t67c15a8v3je5ahuhrimtm1m', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"c48d42290540c4c4486325a8\\\";token_time_expiry|i:1549983135;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549981064;login_time_expiry|i:1549983135;login_status|i:1;\"', 1549983375, 1549981935),
('rbrsoafm0rjqpde7095l5rj04k', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"1ed5bb727491d25656cb0c9d\\\";token_time_expiry|i:1549806881;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1549800296;login_time_expiry|i:1549806881;login_status|i:1;\"', 1549807121, 1549805681),
('sgjq97ctnnpo766o5e83a2ctdm', '\"user|a:0:{}\"', 1549789211, 1549787771),
('t76eo93ei1aiup128f76l49tei', '\"token|s:24:\\\"5b18ddc3092316fc3ce6e911\\\";token_time_expiry|i:1549796150;user|a:0:{}\"', 1549796391, 1549794951),
('tl9irmu13pgsngrd6jbh5khvqe', '\"user|a:0:{}\"', 1549641999, 1549640559),
('tvm7pk06smrcsas9qordds963k', '\"user|a:3:{s:7:\\\"user_id\\\";s:1:\\\"2\\\";s:5:\\\"email\\\";s:15:\\\"admin@admin.com\\\";s:6:\\\"status\\\";s:1:\\\"1\\\";}token|s:24:\\\"5f621e8c4a84424b43dd6c48\\\";token_time_expiry|i:1550081093;user_ip|s:9:\\\"127.0.0.1\\\";user_agent|s:78:\\\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:65.0) Gecko\\/20100101 Firefox\\/65.0\\\";login_time|i:1550076863;login_time_expiry|i:1550081093;login_status|i:1;\"', 1550081333, 1550079893),
('uet6sohlm4aplcdh3ld76mvl2r', '\"user|a:0:{}\"', 1549627011, 1549625571),
('uhu2n3kqdfdtr1fv2rg3c6f53c', '\"user|a:0:{}\"', 1549975520, 1549974080),
('vdmtaiebne4ni67fl9puht4u6i', '\"\"', 1549972578, 1549971138);

-- --------------------------------------------------------

--
-- Table structure for table `stock_status`
--

CREATE TABLE `stock_status` (
  `stock_status_id` int(10) UNSIGNED NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(64) NOT NULL,
  `user_group_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `image` varchar(64) NOT NULL,
  `code` varchar(244) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL,
  `date_added` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `user_group_id`, `first_name`, `last_name`, `image`, `code`, `ip`, `status`, `date_added`) VALUES
(2, 'admin@admin.com', '$2y$10$jy4jgrvoJhhoYZLTI5qTj.C4VVgj1SyKfkocpFsmZAZ6xR7iJMmJa', 1, 'Hossein', 'Shirejoni', '', '', '127.0.0.1', 1, 1548908771);

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `user_group_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `permission` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Admin', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `weight`
--

CREATE TABLE `weight` (
  `weight_id` int(10) UNSIGNED NOT NULL,
  `value` decimal(15,8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `weight_language`
--

CREATE TABLE `weight_language` (
  `weight_id` int(11) NOT NULL,
  `language_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `unit` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attribute`
--
ALTER TABLE `attribute`
  ADD PRIMARY KEY (`attribute_id`),
  ADD KEY `attribute_group_id` (`attribute_group_id`);

--
-- Indexes for table `attribute_group`
--
ALTER TABLE `attribute_group`
  ADD PRIMARY KEY (`attribute_group_id`);

--
-- Indexes for table `attribute_group_language`
--
ALTER TABLE `attribute_group_language`
  ADD PRIMARY KEY (`language_id`,`attribute_group_id`),
  ADD KEY `attribute_group_id` (`attribute_group_id`);

--
-- Indexes for table `attribute_language`
--
ALTER TABLE `attribute_language`
  ADD PRIMARY KEY (`attribute_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `category_filter`
--
ALTER TABLE `category_filter`
  ADD PRIMARY KEY (`category_id`,`filter_id`),
  ADD KEY `filter_id` (`filter_id`);

--
-- Indexes for table `category_language`
--
ALTER TABLE `category_language`
  ADD PRIMARY KEY (`category_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `category_path`
--
ALTER TABLE `category_path`
  ADD PRIMARY KEY (`category_id`,`path_id`),
  ADD KEY `path_id` (`path_id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `filter`
--
ALTER TABLE `filter`
  ADD PRIMARY KEY (`filter_id`),
  ADD KEY `filter_group_id` (`filter_group_id`);

--
-- Indexes for table `filter_group`
--
ALTER TABLE `filter_group`
  ADD PRIMARY KEY (`filter_group_id`);

--
-- Indexes for table `filter_group_langauge`
--
ALTER TABLE `filter_group_langauge`
  ADD PRIMARY KEY (`filter_group_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `filter_language`
--
ALTER TABLE `filter_language`
  ADD PRIMARY KEY (`filter_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `langauge`
--
ALTER TABLE `langauge`
  ADD PRIMARY KEY (`language_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `length`
--
ALTER TABLE `length`
  ADD PRIMARY KEY (`length_id`);

--
-- Indexes for table `length_language`
--
ALTER TABLE `length_language`
  ADD PRIMARY KEY (`length_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `manufacturer`
--
ALTER TABLE `manufacturer`
  ADD PRIMARY KEY (`manufacturer_id`),
  ADD UNIQUE KEY `url` (`url`);

--
-- Indexes for table `manufacturer_language`
--
ALTER TABLE `manufacturer_language`
  ADD PRIMARY KEY (`manufacturer_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `option_language`
--
ALTER TABLE `option_language`
  ADD PRIMARY KEY (`option_id`,`language_id`);

--
-- Indexes for table `option_value`
--
ALTER TABLE `option_value`
  ADD PRIMARY KEY (`option_value_id`);

--
-- Indexes for table `option_value_language`
--
ALTER TABLE `option_value_language`
  ADD PRIMARY KEY (`option_value_id`,`language_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `stock_status_id` (`stock_status_id`);

--
-- Indexes for table `product_attribute`
--
ALTER TABLE `product_attribute`
  ADD PRIMARY KEY (`product_id`,`attribute_id`,`language_id`),
  ADD KEY `language_id` (`language_id`),
  ADD KEY `attribute_id` (`attribute_id`);

--
-- Indexes for table `product_discount`
--
ALTER TABLE `product_discount`
  ADD PRIMARY KEY (`product_discount_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_filter`
--
ALTER TABLE `product_filter`
  ADD PRIMARY KEY (`product_id`,`filter_id`),
  ADD KEY `filter_id` (`filter_id`);

--
-- Indexes for table `product_image`
--
ALTER TABLE `product_image`
  ADD PRIMARY KEY (`product_image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_language`
--
ALTER TABLE `product_language`
  ADD PRIMARY KEY (`product_id`,`language_id`);

--
-- Indexes for table `product_option`
--
ALTER TABLE `product_option`
  ADD PRIMARY KEY (`product_option_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `option_id` (`option_id`);

--
-- Indexes for table `product_option_value`
--
ALTER TABLE `product_option_value`
  ADD PRIMARY KEY (`product_option_value_id`),
  ADD KEY `option_id` (`option_id`),
  ADD KEY `option_value_id` (`option_value_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `product_option_id` (`product_option_id`);

--
-- Indexes for table `product_related`
--
ALTER TABLE `product_related`
  ADD PRIMARY KEY (`product_id`,`related_id`),
  ADD KEY `related_id` (`related_id`);

--
-- Indexes for table `product_special`
--
ALTER TABLE `product_special`
  ADD PRIMARY KEY (`product_special_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `stock_status`
--
ALTER TABLE `stock_status`
  ADD PRIMARY KEY (`stock_status_id`,`language_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_group_id` (`user_group_id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`user_group_id`);

--
-- Indexes for table `weight`
--
ALTER TABLE `weight`
  ADD PRIMARY KEY (`weight_id`);

--
-- Indexes for table `weight_language`
--
ALTER TABLE `weight_language`
  ADD PRIMARY KEY (`weight_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attribute`
--
ALTER TABLE `attribute`
  MODIFY `attribute_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `attribute_group`
--
ALTER TABLE `attribute_group`
  MODIFY `attribute_group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `config_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `filter`
--
ALTER TABLE `filter`
  MODIFY `filter_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `filter_group`
--
ALTER TABLE `filter_group`
  MODIFY `filter_group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `langauge`
--
ALTER TABLE `langauge`
  MODIFY `language_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `length`
--
ALTER TABLE `length`
  MODIFY `length_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manufacturer`
--
ALTER TABLE `manufacturer`
  MODIFY `manufacturer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `option`
--
ALTER TABLE `option`
  MODIFY `option_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `option_value`
--
ALTER TABLE `option_value`
  MODIFY `option_value_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_discount`
--
ALTER TABLE `product_discount`
  MODIFY `product_discount_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_image`
--
ALTER TABLE `product_image`
  MODIFY `product_image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_option`
--
ALTER TABLE `product_option`
  MODIFY `product_option_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_option_value`
--
ALTER TABLE `product_option_value`
  MODIFY `product_option_value_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_special`
--
ALTER TABLE `product_special`
  MODIFY `product_special_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_status`
--
ALTER TABLE `stock_status`
  MODIFY `stock_status_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `weight`
--
ALTER TABLE `weight`
  MODIFY `weight_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attribute`
--
ALTER TABLE `attribute`
  ADD CONSTRAINT `attribute_ibfk_1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`attribute_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attribute_group_language`
--
ALTER TABLE `attribute_group_language`
  ADD CONSTRAINT `attribute_group_language_ibfk_1` FOREIGN KEY (`attribute_group_id`) REFERENCES `attribute_group` (`attribute_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attribute_group_language_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attribute_language`
--
ALTER TABLE `attribute_language`
  ADD CONSTRAINT `attribute_language_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attribute_language_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON UPDATE CASCADE;

--
-- Constraints for table `category_filter`
--
ALTER TABLE `category_filter`
  ADD CONSTRAINT `category_filter_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category_filter_ibfk_2` FOREIGN KEY (`filter_id`) REFERENCES `filter` (`filter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `category_language`
--
ALTER TABLE `category_language`
  ADD CONSTRAINT `category_language_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category_language_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `category_path`
--
ALTER TABLE `category_path`
  ADD CONSTRAINT `category_path_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `category_path_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `filter`
--
ALTER TABLE `filter`
  ADD CONSTRAINT `filter_ibfk_1` FOREIGN KEY (`filter_group_id`) REFERENCES `filter_group` (`filter_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `filter_group_langauge`
--
ALTER TABLE `filter_group_langauge`
  ADD CONSTRAINT `filter_group_langauge_ibfk_1` FOREIGN KEY (`filter_group_id`) REFERENCES `filter_group` (`filter_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filter_group_langauge_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `filter_language`
--
ALTER TABLE `filter_language`
  ADD CONSTRAINT `filter_language_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `filter_language_ibfk_2` FOREIGN KEY (`filter_id`) REFERENCES `filter` (`filter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `length_language`
--
ALTER TABLE `length_language`
  ADD CONSTRAINT `length_language_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `manufacturer_language`
--
ALTER TABLE `manufacturer_language`
  ADD CONSTRAINT `manufacturer_language_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturer` (`manufacturer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `manufacturer_language_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`stock_status_id`) REFERENCES `stock_status` (`stock_status_id`);

--
-- Constraints for table `product_attribute`
--
ALTER TABLE `product_attribute`
  ADD CONSTRAINT `product_attribute_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_attribute_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_attribute_ibfk_3` FOREIGN KEY (`attribute_id`) REFERENCES `attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_discount`
--
ALTER TABLE `product_discount`
  ADD CONSTRAINT `product_discount_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_filter`
--
ALTER TABLE `product_filter`
  ADD CONSTRAINT `product_filter_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_filter_ibfk_2` FOREIGN KEY (`filter_id`) REFERENCES `filter` (`filter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_image`
--
ALTER TABLE `product_image`
  ADD CONSTRAINT `product_image_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_option`
--
ALTER TABLE `product_option`
  ADD CONSTRAINT `product_option_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_option_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_option_value`
--
ALTER TABLE `product_option_value`
  ADD CONSTRAINT `product_option_value_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_option_value_ibfk_2` FOREIGN KEY (`option_value_id`) REFERENCES `option_value` (`option_value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_option_value_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_option_value_ibfk_4` FOREIGN KEY (`product_option_id`) REFERENCES `product_option` (`product_option_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_related`
--
ALTER TABLE `product_related`
  ADD CONSTRAINT `product_related_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_related_ibfk_2` FOREIGN KEY (`related_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_special`
--
ALTER TABLE `product_special`
  ADD CONSTRAINT `product_special_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_group_id`) REFERENCES `user_group` (`user_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `weight_language`
--
ALTER TABLE `weight_language`
  ADD CONSTRAINT `weight_language_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `langauge` (`language_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
