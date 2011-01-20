-- phpMyAdmin SQL Dump
-- version 3.3.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 30, 2010 at 08:48 PM
-- Server version: 5.1.48
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `imgboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `uri` varchar(8) NOT NULL,
  `title` varchar(20) NOT NULL,
  `subtitle` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `uri`, `title`, `subtitle`) VALUES
(1, 'b', 'Beta', 'In development.');

-- --------------------------------------------------------

--
-- Table structure for table `posts_b`
--

CREATE TABLE IF NOT EXISTS `posts_b` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread` int(11) DEFAULT NULL,
  `subject` varchar(40) NOT NULL,
  `email` varchar(30) NOT NULL,
  `name` varchar(25) NOT NULL,
  `trip` varchar(15) DEFAULT NULL,
  `body` text NOT NULL,
  `time` int(11) NOT NULL,
  `bump` int(11) DEFAULT NULL,
  `thumb` varchar(50) DEFAULT NULL,
  `thumbwidth` int(11) DEFAULT NULL,
  `thumbheight` int(11) DEFAULT NULL,
  `file` varchar(50) DEFAULT NULL,
  `filewidth` int(11) DEFAULT NULL,
  `fileheight` int(11) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `filename` varchar(30) DEFAULT NULL,
  `filehash` varchar(32) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `sticky` int(1) NOT NULL,
  `locked` int(1) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `posts_b`
--

-- --------------------------------------------------------

--
-- Table structure for table `mods`
--

CREATE TABLE IF NOT EXISTS `mods` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` char(40) NOT NULL COMMENT 'SHA1',
  `type` smallint(1) NOT NULL COMMENT '0: janitor, 1: mod, 2: admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mods`
--

INSERT INTO `mods` (`id`, `username`, `password`, `type`) VALUES
(1, 'admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 2);


-- --------------------------------------------------------

--
-- Table structure for table `bans`
--


CREATE TABLE IF NOT EXISTS  `bans` (
  `ip` varchar( 15 ) NOT NULL ,
  `mod` int NOT NULL COMMENT  'which mod made the ban',
  `set` int NOT NULL,
  `expires` int NULL,
  `reason` text NULL
) ENGINE = InnoDB;


CREATE TABLE `robot` (
`hash` VARCHAR( 40 ) NOT NULL COMMENT  'SHA1'
) ENGINE = INNODB; */


CREATE TABLE `mutes` (
`ip` VARCHAR( 15 ) NOT NULL ,
`time` INT NOT NULL
) ENGINE = MYISAM ;