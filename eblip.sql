-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2010 at 02:43 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `blip`
--

-- --------------------------------------------------------

--
-- Table structure for table `eblip_alt_emails`
--

CREATE TABLE IF NOT EXISTS `eblip_alt_emails` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `user_id` mediumint(8) unsigned NOT NULL,
  `email` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

--
-- Table structure for table `eblip_blips`
--

CREATE TABLE IF NOT EXISTS `eblip_blips` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `datetime` datetime NOT NULL,
  `user_id` mediumint(8) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1826 ;

-- --------------------------------------------------------

--
-- Table structure for table `eblip_errors`
--

CREATE TABLE IF NOT EXISTS `eblip_errors` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `time` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=143 ;

-- --------------------------------------------------------

--
-- Table structure for table `eblip_users`
--

CREATE TABLE IF NOT EXISTS `eblip_users` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `login` varchar(20) NOT NULL,
  `passwd` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `count` mediumint(8) unsigned NOT NULL,
  `phone_no` varchar(9) NOT NULL,
  `phone_status` smallint(5) unsigned NOT NULL,
  `phone_code` varchar(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `count` (`count`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=146 ;
