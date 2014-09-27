-- phpMyAdmin SQL Dump
-- version 2.8.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Sep 27, 2014 at 04:22 AM
-- Server version: 5.0.22
-- PHP Version: 5.1.4
-- 
-- Database: `dexter`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `links`
-- 

CREATE TABLE `links` (
  `word_from` varchar(45) character set utf8 collate utf8_unicode_ci NOT NULL,
  `word_to` varchar(45) character set utf8 collate utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  UNIQUE KEY `uniquelink` (`word_from`,`word_to`),
  KEY `word_to` (`word_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `words`
-- 

CREATE TABLE `words` (
  `word` varchar(45) character set utf8 collate utf8_unicode_ci NOT NULL,
  `total_freq` int(11) NOT NULL default '1',
  PRIMARY KEY  (`word`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `links`
-- 
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`word_from`) REFERENCES `words` (`word`),
  ADD CONSTRAINT `links_ibfk_2` FOREIGN KEY (`word_to`) REFERENCES `words` (`word`);
