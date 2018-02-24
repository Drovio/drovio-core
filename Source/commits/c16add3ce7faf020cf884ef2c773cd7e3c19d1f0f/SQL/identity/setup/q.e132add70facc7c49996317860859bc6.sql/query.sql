-- Set some settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database: `{db_name}` and use it
CREATE DATABASE `{db_name}` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;
USE `{db_name}`;

-- Table structure for table `ID_account`
CREATE TABLE IF NOT EXISTS `ID_account` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(100) COLLATE utf8_general_ci NOT NULL,
	`description` text COLLATE utf8_general_ci NOT NULL,
	`username` varchar(20) COLLATE utf8_general_ci DEFAULT NULL,
	`password` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
	`administrator` tinyint(1) NOT NULL,
	`parent_id` int(11) unsigned DEFAULT NULL,
	`locked` tinyint(1) NOT NULL DEFAULT '0',
	`reset` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`),
	KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB	DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=63 ;

-- Table structure for table `ID_accountSession`
CREATE TABLE IF NOT EXISTS `ID_accountSession` (
	`id` int(11) NOT NULL,
	`account_id` int(11) unsigned NOT NULL,
	`salt` varchar(50) NOT NULL,
	`ip` varchar(50) DEFAULT NULL,
	`lastAccess` varchar(15) DEFAULT NULL,
	`userAgent` varchar(150) NOT NULL,
	`rememberme` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`,`account_id`),
	KEY `accountID` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table structure for table `ID_person`
CREATE TABLE IF NOT EXISTS `ID_person` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`firstname` varchar(100) NOT NULL,
	`middle_name` varchar(100) DEFAULT NULL,
	`lastname` varchar(100) NOT NULL,
	`mail` varchar(200) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `mail` (`mail`)
) ENGINE=InnoDB	DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;

-- Table structure for table `ID_personToAccount`
CREATE TABLE IF NOT EXISTS `ID_personToAccount` (
	`person_id` int(11) unsigned NOT NULL,
	`account_id` int(11) unsigned NOT NULL,
	PRIMARY KEY (`person_id`,`account_id`),
	KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Constraints for table `ID_account`
ALTER TABLE `ID_account`
	ADD CONSTRAINT `ID_account_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `ID_accountSession`
ALTER TABLE `ID_accountSession`
	ADD CONSTRAINT `ID_accountSession_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `ID_personToAccount`
ALTER TABLE `ID_personToAccount`
	ADD CONSTRAINT `ID_personToAccount_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `ID_personToAccount_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ID_person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;