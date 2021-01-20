-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 20, 2021 at 09:25 AM
-- Server version: 8.0.22-0ubuntu0.20.04.3
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gsm`
--

-- --------------------------------------------------------

--
-- Table structure for table `base_servers`
--

DROP TABLE IF EXISTS `base_servers`;
CREATE TABLE `base_servers` (
  `id` int NOT NULL COMMENT 'id',
  `fname` varchar(50) NOT NULL,
  `url` varchar(250) NOT NULL,
  `port` int NOT NULL,
  `name` varchar(250) NOT NULL,
  `ip` varchar(25) NOT NULL,
  `base_ip` varchar(25) NOT NULL COMMENT 'base server IP',
  `enabled` tinyint(1) NOT NULL,
  `extraip` int NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
CREATE TABLE `commands` (
  `id` int NOT NULL,
  `server_id` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `action` varchar(256) NOT NULL,
  `command` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_run` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `game_servers`
--

DROP TABLE IF EXISTS `game_servers`;
CREATE TABLE `game_servers` (
  `id` int NOT NULL,
  `game_id` int NOT NULL,
  `server_id` int NOT NULL,
  `buildid` varchar(25) NOT NULL,
  `type` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `run_stub` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `game_name` varchar(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `default_hostname` varchar(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `default_path` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `install_dir` varchar(100) NOT NULL,
  `default_max_players` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `jquery`
--

DROP TABLE IF EXISTS `jquery`;
CREATE TABLE `jquery` (
  `id` int NOT NULL,
  `template_id` varchar(50) NOT NULL,
  `jquery` mediumtext NOT NULL,
  `html` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `id` int NOT NULL,
  `ip` bigint NOT NULL COMMENT 'IP as number ',
  `steam_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'steamID\r\n',
  `steam_id64` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` blob NOT NULL COMMENT 'current username',
  `first_log_on` int NOT NULL,
  `last_log_on` int NOT NULL,
  `log_ons` int NOT NULL,
  `continent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'continent',
  `country_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n/a',
  `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Country',
  `region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'region',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'city',
  `flag` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'encoded flag graphic',
  `time_zone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `threat` int NOT NULL COMMENT 'overall threat level',
  `threat_level` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'csv threat levels\r\n',
  `server` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'csv games played'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `id` int NOT NULL,
  `uid` int NOT NULL,
  `host_name` varchar(40) NOT NULL COMMENT 'internal name',
  `server_name` varchar(100) NOT NULL COMMENT 'returned server name',
  `game` varchar(25) NOT NULL,
  `app_id` int NOT NULL,
  `server_id` int NOT NULL,
  `buildid` varchar(25) NOT NULL,
  `server_update` varchar(25) NOT NULL,
  `host` varchar(20) NOT NULL,
  `location` varchar(256) NOT NULL,
  `port` int NOT NULL,
  `client_port` int NOT NULL,
  `source_port` int NOT NULL,
  `type` varchar(25) NOT NULL,
  `logo` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `status` varchar(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `startcmd` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `server_password` varchar(50) NOT NULL,
  `rcon_password` varchar(20) NOT NULL,
  `default_map` varchar(100) DEFAULT NULL,
  `max_players` int NOT NULL,
  `running` int NOT NULL,
  `starttime` varchar(25) NOT NULL,
  `rbuildid` varchar(25) NOT NULL,
  `rserver_update` varchar(25) NOT NULL,
  `touch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `map_prefix` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `sid` int NOT NULL,
  `s_order` int NOT NULL,
  `type` int NOT NULL,
  `area` varchar(25) NOT NULL,
  `title` varchar(100) NOT NULL,
  `value` varchar(128) NOT NULL,
  `s_desc` text NOT NULL,
  `display` int NOT NULL,
  `setting_type` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `software`
--

DROP TABLE IF EXISTS `software`;
CREATE TABLE `software` (
  `ip` varchar(20) NOT NULL,
  `kernel` varchar(20) NOT NULL,
  `php` varchar(20) NOT NULL,
  `screen` varchar(20) NOT NULL,
  `glibc` varchar(20) NOT NULL,
  `mysql` varchar(20) NOT NULL,
  `apache` varchar(20) NOT NULL,
  `curl` varchar(20) NOT NULL,
  `nginx` varchar(20) NOT NULL,
  `quota` varchar(20) NOT NULL,
  `postfix` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temp`
--

DROP TABLE IF EXISTS `temp`;
CREATE TABLE `temp` (
  `id` int NOT NULL,
  `ip` bigint NOT NULL COMMENT 'IP as number ',
  `steam_id` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'steamID\r\n',
  `steam_id64` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` blob NOT NULL COMMENT 'current username',
  `first_log_on` int NOT NULL,
  `last_log_on` int NOT NULL,
  `log_ons` int NOT NULL,
  `continent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'continent',
  `country_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n/a',
  `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Country',
  `region` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'region',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'city',
  `flag` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'encoded flag graphic',
  `time_zone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `threat` int NOT NULL COMMENT 'overall threat level',
  `threat_level` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'csv threat levels\r\n',
  `server` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'csv games played'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL,
  `nid` varchar(32) NOT NULL DEFAULT '',
  `username` varchar(65) NOT NULL DEFAULT '',
  `password` varchar(65) NOT NULL DEFAULT '',
  `level` enum('banned','user','admin','mod','smod') DEFAULT 'user',
  `ip` varchar(50) DEFAULT NULL,
  `currentip` varchar(20) DEFAULT NULL,
  `regdate` bigint DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `steamid` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `base_servers`
--
ALTER TABLE `base_servers`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `commands`
--
ALTER TABLE `commands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `game_servers`
--
ALTER TABLE `game_servers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_id` (`game_id`);

--
-- Indexes for table `jquery`
--
ALTER TABLE `jquery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `steam-id` (`steam_id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`sid`),
  ADD UNIQUE KEY `sid` (`sid`);

--
-- Indexes for table `software`
--
ALTER TABLE `software`
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indexes for table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `steam-id` (`steam_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `base_servers`
--
ALTER TABLE `base_servers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'id';

--
-- AUTO_INCREMENT for table `commands`
--
ALTER TABLE `commands`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_servers`
--
ALTER TABLE `game_servers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jquery`
--
ALTER TABLE `jquery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `sid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp`
--
ALTER TABLE `temp`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
