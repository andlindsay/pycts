-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: 128.153.5.119
-- Generation Time: Mar 07, 2014 at 01:39 PM
-- Server version: 5.0.95
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `researchcredit_points_testing`
--

-- --------------------------------------------------------

CREATE database researchcredit_points;

--
-- Table structure for table `blocks`
--

CREATE TABLE IF NOT EXISTS `blocks` (
  `block` int(1) NOT NULL,
  `u_time` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE IF NOT EXISTS `points` (
  `p_id` mediumint(9) NOT NULL auto_increment,
  `u_ad` varchar(16) NOT NULL,
  `u_amount` decimal(11,1) NOT NULL,
  `st_id` int(11) NOT NULL,
  `u_add_ad` varchar(16) NOT NULL,
  `u_rem_ad` varchar(16) default NULL,
  `time_add` int(10) unsigned NOT NULL,
  `block_num` int(1) NOT NULL default '1',
  `time_rem` int(10) unsigned default NULL,
  `desc_add` varchar(128) default NULL,
  `desc_rem` varchar(128) default NULL,
  PRIMARY KEY  (`p_id`),
  KEY `s_ad` (`u_ad`),
  KEY `st_id` (`st_id`),
  KEY `u_add_ad` (`u_add_ad`),
  KEY `u_rem_ad` (`u_rem_ad`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7176 ;

-- --------------------------------------------------------

--
-- Table structure for table `studies`
--

CREATE TABLE IF NOT EXISTS `studies` (
  `st_id` int(11) NOT NULL auto_increment,
  `st_irb` varchar(16) NOT NULL,
  `st_desc` varchar(128) NOT NULL,
  `st_credits` decimal(11,1) NOT NULL,
  `st_flyer` varchar(128) NOT NULL,
  `st_visible` int(11) NOT NULL,
  PRIMARY KEY  (`st_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=131 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `u_ad` varchar(16) NOT NULL,
  `u_role` int(1) NOT NULL default '2',
  `u_lname` varchar(64) NOT NULL,
  `u_fname` varchar(64) NOT NULL,
  `u_prof` varchar(64) NOT NULL,
  PRIMARY KEY  (`u_ad`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

