-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2015 at 03:25 AM
-- Server version: 5.6.24
-- PHP Version: 5.5.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hw_projects`
--

-- --------------------------------------------------------

--
-- Table structure for table `cpanel-accts`
--

CREATE TABLE IF NOT EXISTS `cpanel-accts` (
  `id` int(11) NOT NULL,
  `cpanel_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cpanel_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cpanel_host` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cpanel_domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cpanel_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `root` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cpanel-accts`
--

INSERT INTO `cpanel-accts` (`id`, `cpanel_user`, `cpanel_pass`, `cpanel_host`, `cpanel_domain`, `cpanel_email`, `root`) VALUES
(4, 'hwvn', 'KylPWGdwTVU9ZzV4', '216.172.164.142', 'hoangweb.net', 'quachhoang_2005@yahoo.com', 0),
(7, 'vietcodex', '', '216.172.164.142', 'vietcodex.com', 'laptrinhweb123@gmail.com', 0),
(8, 'dayhocwp', '', '216.172.164.142', 'dayhocwordpress.com', 'laptrinhweb123@gmail.com', 0),
(9, 'bcmpt', '', '216.172.164.142', 'baochuamienphi.com', 'laptrinhweb123@gmail.com', 0),
(10, 'hnstreetfooda', '', '216.172.164.142', 'hanoistreetfoodadventure.com', 'laptrinhweb123@gmail.com', 0),
(11, 'codexedu', '', '216.172.164.142', 'vietcodex.edu.vn', 'laptrinhweb123@gmail.com', 0),
(12, 'dvfcvn', '', '216.172.164.142', 'dvfc.com.vn', 'maiphuong070@gmail.com', 0),
(13, 'nanhthom', '', '216.172.164.142', 'nguyenanhthom.com', 'laptrinhweb123@gmail.com', 0),
(14, 'sim6879', '', '216.172.164.142', 'sim6879.com', 'laptrinhweb123@gmail.com', 0),
(15, 'lbdaihocvn', '', '216.172.164.142', 'lambangdaihocvn.net', 'congtyhanoiland@gmail.com', 0),
(16, 'gtravelvn', '', '216.172.164.142', 'greentravelvn.com', 'sapatours07@gmail.com', 0),
(17, 'vtdsmba', '', '216.172.164.142', 'vantaiduongsatmienbac.com', 'laptrinhweb123@gmail.com', 0),
(18, 'dhthietkw', '', '216.172.164.142', 'dayhocthietkeweb.net', 'laptrinhweb123@gmail.com', 0),
(19, 'laptrangweb', '', '216.172.164.142', 'laptrangweb.info', 'laptrinhweb123@gmail.com', 0),
(20, 'dayhocpts', '', '216.172.164.142', 'dayhocphotoshop.com', 'laptrinhweb123@gmail.com', 0),
(21, 'vuagems', '', '216.172.164.142', 'vuagems.com', 'hoangduchai@gmail.com', 0),
(22, 'hoangweb', '', '216.172.164.142', 'hoangweb.com', 'laptrinhweb123@gmail.com, hoangsoft90@gmail.com', 0),
(23, 'tcbhvn', '', '216.172.164.142', 'tracuubaohanh.com', 'nguyenthang2202@gmail.com', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cpanel-dbusers`
--

CREATE TABLE IF NOT EXISTS `cpanel-dbusers` (
  `id` int(11) NOT NULL,
  `cpid` int(11) NOT NULL,
  `db` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `dbuser` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `dbpass` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cpanel-dbusers`
--

INSERT INTO `cpanel-dbusers` (`id`, `cpid`, `db`, `dbuser`, `dbpass`) VALUES
(2, 4, 'db5', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `cpanel-ftp`
--

CREATE TABLE IF NOT EXISTS `cpanel-ftp` (
  `cpid` int(11) NOT NULL,
  `ftp_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ftp_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cpanel-ftp`
--

INSERT INTO `cpanel-ftp` (`cpid`, `ftp_user`, `ftp_pass`, `path`) VALUES
(4, 'user3', 'aFpScnN5TSNWQEVFYzZZ', ''),
(4, 'user2', 'a2RmaGRrc2diZGZqZ2Jqa2RzZmdzZA==', '');

-- --------------------------------------------------------

--
-- Table structure for table `cpanel-tokens`
--

CREATE TABLE IF NOT EXISTS `cpanel-tokens` (
  `cpid` int(11) NOT NULL,
  `token` text COLLATE utf8_unicode_ci NOT NULL,
  `expires` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cpanel-tokens`
--

INSERT INTO `cpanel-tokens` (`cpid`, `token`, `expires`) VALUES
(1, 'sdgdfhffgjghkhjkljhl', '1440434861'),
(4, '/cpsess2184733406', '1440473447'),
(23, '/cpsess2385997015', '1440478157');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cpanel-accts`
--
ALTER TABLE `cpanel-accts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cpanel-dbusers`
--
ALTER TABLE `cpanel-dbusers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cpanel-accts`
--
ALTER TABLE `cpanel-accts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `cpanel-dbusers`
--
ALTER TABLE `cpanel-dbusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
