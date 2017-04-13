-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 17, 2017 at 02:56 PM
-- Server version: 5.6.24
-- PHP Version: 5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `solvonauts`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_link`
--

CREATE TABLE IF NOT EXISTS `activity_link` (
  `id` bigint(20) NOT NULL,
  `link_id` int(11) NOT NULL,
  `time_clicked` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `activity_search`
--

CREATE TABLE IF NOT EXISTS `activity_search` (
  `id` bigint(20) NOT NULL,
  `term` varchar(100) COLLATE utf8_bin NOT NULL,
  `results` bigint(20) NOT NULL,
  `ip` varchar(25) COLLATE utf8_bin NOT NULL,
  `time_searched` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `link_index`
--

CREATE TABLE IF NOT EXISTS `link_index` (
  `id` bigint(20) NOT NULL,
  `link_id` bigint(20) NOT NULL,
  `link` varchar(300) COLLATE utf8_bin NOT NULL,
  `title` varchar(800) COLLATE utf8_bin NOT NULL,
  `description` varchar(800) COLLATE utf8_bin NOT NULL,
  `subject` varchar(800) COLLATE utf8_bin NOT NULL,
  `license` varchar(800) COLLATE utf8_bin NOT NULL,
  `site_address` varchar(255) COLLATE utf8_bin NOT NULL,
  `last_updated` bigint(20) NOT NULL,
  `first_harvested` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `link_table`
--

CREATE TABLE IF NOT EXISTS `link_table` (
  `link_id` bigint(20) NOT NULL,
  `link` varchar(300) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `link_term`
--

CREATE TABLE IF NOT EXISTS `link_term` (
  `link_term_id` bigint(20) NOT NULL,
  `link_id` bigint(20) NOT NULL,
  `term_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `node_data`
--

CREATE TABLE IF NOT EXISTS `node_data` (
  `node_id` bigint(20) NOT NULL,
  `node_value` varchar(800) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `node_term`
--

CREATE TABLE IF NOT EXISTS `node_term` (
  `term_id` bigint(20) NOT NULL,
  `term` varchar(30) COLLATE utf8_bin NOT NULL,
  `node_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `oer_site_list`
--

CREATE TABLE IF NOT EXISTS `oer_site_list` (
  `index_link` int(11) NOT NULL,
  `site_address` varchar(500) DEFAULT NULL,
  `site_licence` varchar(255) NOT NULL,
  `feed_status` varchar(10) NOT NULL,
  `url_type` varchar(20) NOT NULL,
  `items_harvested` bigint(20) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_link`
--
ALTER TABLE `activity_link`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_search`
--
ALTER TABLE `activity_search`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `link_index`
--
ALTER TABLE `link_index`
  ADD PRIMARY KEY (`id`),
  ADD KEY `title` (`title`(255)),
  ADD KEY `description` (`description`(255)),
  ADD KEY `subject` (`subject`(255)),
  ADD KEY `link` (`link`(255)),
  ADD KEY `link_id` (`link_id`);

--
-- Indexes for table `link_table`
--
ALTER TABLE `link_table`
  ADD PRIMARY KEY (`link_id`),
  ADD KEY `link` (`link`(255));

--
-- Indexes for table `link_term`
--
ALTER TABLE `link_term`
  ADD PRIMARY KEY (`link_term_id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `term_id` (`term_id`);

--
-- Indexes for table `node_data`
--
ALTER TABLE `node_data`
  ADD PRIMARY KEY (`node_id`),
  ADD KEY `node_value` (`node_value`(255));

--
-- Indexes for table `node_term`
--
ALTER TABLE `node_term`
  ADD PRIMARY KEY (`term_id`),
  ADD UNIQUE KEY `Unique check` (`term`,`node_id`),
  ADD KEY `term` (`term`),
  ADD KEY `node_id` (`node_id`);

--
-- Indexes for table `oer_site_list`
--
ALTER TABLE `oer_site_list`
  ADD PRIMARY KEY (`index_link`),
  ADD UNIQUE KEY `site_address` (`site_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_link`
--
ALTER TABLE `activity_link`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `activity_search`
--
ALTER TABLE `activity_search`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `link_index`
--
ALTER TABLE `link_index`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `link_table`
--
ALTER TABLE `link_table`
  MODIFY `link_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `link_term`
--
ALTER TABLE `link_term`
  MODIFY `link_term_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_data`
--
ALTER TABLE `node_data`
  MODIFY `node_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `node_term`
--
ALTER TABLE `node_term`
  MODIFY `term_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `oer_site_list`
--
ALTER TABLE `oer_site_list`
  MODIFY `index_link` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=793;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
