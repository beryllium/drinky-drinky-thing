-- phpMyAdmin SQL Dump
-- version 3.1.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 18, 2014 at 11:52 PM
-- Server version: 5.1.32
-- PHP Version: 5.4.22

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `drinky`
--

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE IF NOT EXISTS `places` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `address1` varchar(255) NOT NULL,
      `address2` varchar(255) NOT NULL,
      `city` varchar(255) NOT NULL,
      `postal` varchar(10) NOT NULL,
      `mail_address1` varchar(255) NOT NULL,
      `mail_address2` varchar(255) NOT NULL,
      `mail_city` varchar(255) NOT NULL,
      `mail_prov` varchar(255) NOT NULL,
      `mail_postal` varchar(10) NOT NULL,
      `type` varchar(255) NOT NULL,
      `capacity` int(11) NOT NULL,
      `date_added` datetime NOT NULL,
      `latitude` decimal(18,12) NOT NULL,
      `longitude` decimal(18,12) NOT NULL,
      `geocode_raw` text NOT NULL,
      `geocode_address` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

