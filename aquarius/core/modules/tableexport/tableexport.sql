-- phpMyAdmin SQL Dump
-- version 2.9.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 13, 2007 at 11:28 AM
-- Server version: 5.0.24
-- PHP Version: 4.4.2
-- 
-- Database: `svfasfc_main`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `exambooking`
-- 
CREATE TABLE IF NOT EXISTS `exambooking` (
  `ID` int(11) NOT NULL auto_increment,
  `AHVNumber` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ExternalReference` varchar(256) collate utf8_unicode_ci default NULL,
  `ContactTitle1` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ContactFirstName` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ContactLastName` varchar(256) collate utf8_unicode_ci NOT NULL,
  `Address` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ZIP` varchar(256) collate utf8_unicode_ci NOT NULL,
  `Town` varchar(256) collate utf8_unicode_ci NOT NULL,
  `Language` varchar(256) collate utf8_unicode_ci NOT NULL,
  `Phone` varchar(20) collate utf8_unicode_ci NOT NULL,
  `Mobile` varchar(20) collate utf8_unicode_ci default NULL,
  `Email` varchar(256) collate utf8_unicode_ci NOT NULL,
  `DateOfBirth` varchar(256) collate utf8_unicode_ci default NULL,
  `OriginPlace` varchar(256) collate utf8_unicode_ci NOT NULL,
  `CountryOfOrigin` varchar(256) collate utf8_unicode_ci NOT NULL,
  `CompanyName` varchar(256) collate utf8_unicode_ci default NULL,
  `CompanyAddress` varchar(256) collate utf8_unicode_ci default NULL,
  `CompanyZIP` varchar(256) collate utf8_unicode_ci default NULL,
  `CompanyTown` varchar(256) collate utf8_unicode_ci default NULL,
  `MilitaryExamName` varchar(256) collate utf8_unicode_ci NOT NULL,
  `MilitaryExamShortcut` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ExamStatus` varchar(256) collate utf8_unicode_ci NOT NULL,
  `SerieDesignation` varchar(256) collate utf8_unicode_ci NOT NULL,
  `ExamDate` varchar(256) collate utf8_unicode_ci NOT NULL,
  `SchoolName` varchar(256) collate utf8_unicode_ci NOT NULL,
  `SchoolTown` varchar(256) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;