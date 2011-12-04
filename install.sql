-- phpMyAdmin SQL Dump
-- version 3.4.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 09, 2011 at 04:03 AM
-- Server version: 5.1.58
-- PHP Version: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tinyboard`
--

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `mod` int(11) NOT NULL COMMENT 'which mod made the ban',
  `set` int(11) NOT NULL,
  `expires` int(11) DEFAULT NULL,
  `reason` text,
  `board` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `boards`
--

CREATE TABLE IF NOT EXISTS `boards` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `uri` varchar(8) NOT NULL,
  `title` varchar(20) NOT NULL,
  `subtitle` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`uri`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `boards`
--

INSERT INTO `boards` (`id`, `uri`, `title`, `subtitle`) VALUES
(1, 'b', 'Beta', 'In development.');

-- --------------------------------------------------------

--
-- Table structure for table `ip_notes`
--

CREATE TABLE IF NOT EXISTS `ip_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `mod` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `body` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `modlogs`
--

CREATE TABLE IF NOT EXISTS `modlogs` (
  `mod` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `board` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `text` text NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mods`
--

CREATE TABLE IF NOT EXISTS `mods` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` char(40) NOT NULL COMMENT 'SHA1',
  `type` smallint(1) NOT NULL COMMENT '0: janitor, 1: mod, 2: admin',
  `boards` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `mods`
--

INSERT INTO `mods` (`id`, `username`, `password`, `type`, `boards`) VALUES
(1, 'admin', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', 2, '*');

-- --------------------------------------------------------

--
-- Table structure for table `mutes`
--

CREATE TABLE IF NOT EXISTS `mutes` (
  `ip` varchar(45) NOT NULL,
  `time` int(11) NOT NULL,
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `time` int(11) NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `noticeboard`
--

CREATE TABLE IF NOT EXISTS `noticeboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mod` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pms`
--

CREATE TABLE IF NOT EXISTS `pms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `unread` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `posts_b`
--

CREATE TABLE IF NOT EXISTS `posts_b` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread` int(11) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `trip` varchar(15) DEFAULT NULL,
  `capcode` varchar(50) DEFAULT NULL,
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
  `filename` text,
  `filehash` text,
  `password` varchar(20) DEFAULT NULL,
  `ip` varchar(45) NOT NULL,
  `sticky` int(1) NOT NULL,
  `locked` int(1) NOT NULL,
  `sage` int(1) NOT NULL,
  `embed` text,
  UNIQUE KEY `id` (`id`),
  KEY `thread` (`thread`),
  KEY `time` (`time`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `board` smallint(6) NOT NULL,
  `post` int(11) NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `robot`
--

CREATE TABLE IF NOT EXISTS `robot` (
  `hash` varchar(40) NOT NULL COMMENT 'SHA1',
  PRIMARY KEY (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `theme_settings`
--

CREATE TABLE IF NOT EXISTS `theme_settings` (
  `theme` varchar(40) NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `value` text,
  KEY `theme` (`theme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
