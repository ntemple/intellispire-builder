-- phpMyAdmin SQL Dump
-- version 2.6.1-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 16, 2005 at 06:21 PM
-- Server version: 4.1.13
-- PHP Version: 4.3.11
-- 
-- Database: `pdcforge_p`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `purchase_ledger`
-- 

CREATE TABLE IF NOT EXISTS `purchase_ledger` (
  `id` int(11) NOT NULL auto_increment,
  `ts` timestamp NOT NULL,
  `members_id` int(11) NOT NULL default '0',
  `sales_id` int(11) NOT NULL default '0',
  `ipn_id` int(11) NOT NULL default '0',
  `amount` decimal(10,2) NOT NULL default '0.00',
  `mc_currency` char(3) NOT NULL default 'USD',
  `notes` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `purchase_pdc`
-- 

CREATE TABLE IF NOT EXISTS `purchase_pdc` (
  `id` int(11) NOT NULL auto_increment,
  `ts` timestamp NOT NULL,
  `members_id` int(11) default NULL,
  `sales_id` int(11) default NULL,
  `ipn_id` int(11) default NULL,
  `amount` decimal(10,2) NOT NULL default '0.00',
  `mc_currency` char(3) NOT NULL default 'USD',
  `notes` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `purchase_sales`
-- 

CREATE TABLE IF NOT EXISTS `purchase_sales` (
  `id` int(10) NOT NULL auto_increment,
  `ts` timestamp NOT NULL,
  `seller_id` int(10) NOT NULL default '0',
  `aff_id` int(10) NOT NULL default '0',
  `amount` float(10,2) NOT NULL default '0.00',
  `pdc_profit` decimal(10,2) NOT NULL default '0.00',
  `pdc_fee` decimal(10,2) NOT NULL default '0.00',
  `sale_status` enum('ok','cancelled','pending','refund') NOT NULL default 'ok',
  `sale_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `product_id` int(10) NOT NULL default '0',
  `first_name` varchar(64) NOT NULL default '',
  `last_name` varchar(64) NOT NULL default '',
  `email` varchar(127) NOT NULL default '',
  `address` varchar(200) NOT NULL default '',
  `city` varchar(40) NOT NULL default '',
  `state` varchar(40) NOT NULL default '',
  `zip` varchar(20) NOT NULL default '',
  `country` varchar(64) NOT NULL default '',
  `item_name` varchar(200) NOT NULL default '',
  `item_number` varchar(200) NOT NULL default '',
  `aff_opinion` enum('none','not_paid') NOT NULL default 'none',
  `ipn_id` int(11) default '0',
  PRIMARY KEY  (`id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `purchase_session`
-- 

CREATE TABLE IF NOT EXISTS `purchase_session` (
  `id` int(11) NOT NULL auto_increment,
  `ts` timestamp NOT NULL,
  `session` varchar(50) NOT NULL default '',
  `ip` int(10) unsigned NOT NULL default '0',
  `amount` decimal(10,2) NOT NULL default '0.00',
  `aff_id` varchar(50) default NULL,
  `camp_tool` int(11) default NULL,
  `tool` int(11) default '0',
  `product_id` int(11) default NULL,
  `req` varchar(255) NOT NULL default '',
  `ipn_id` int(11) default NULL,
  `valid` tinyint(4) NOT NULL default '0',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `session` (`session`)
);

