-- Set some settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Table structure for table `ID_PM_group`
CREATE TABLE IF NOT EXISTS `ID_PM_group` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(200) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `ID_PM_userGroup`
CREATE TABLE IF NOT EXISTS `ID_PM_accountGroup` (
	`account_id` int(11) unsigned NOT NULL,
	`group_id` int(11) unsigned NOT NULL,
	PRIMARY KEY (`account_id`,`group_id`),
	KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Constraints for table `ID_PM_userGroup`
ALTER TABLE `ID_PM_accountGroup`
	ADD CONSTRAINT `ID_PM_accountGroup_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `ID_PM_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `ID_PM_accountGroup_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;