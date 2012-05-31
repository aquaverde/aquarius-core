-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. April 2012 um 18:59
-- Server Version: 5.0.51
-- PHP-Version: 5.2.6-1+lenny10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `TEST_j3l`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `shorturi`
--

CREATE TABLE IF NOT EXISTS `shorturi` (
  `id` int(11) NOT NULL auto_increment,
  `domain` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  `keyword` varchar(60) character set utf8 collate utf8_bin NOT NULL,
  `redirect` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `keyword` (`keyword`,`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;
