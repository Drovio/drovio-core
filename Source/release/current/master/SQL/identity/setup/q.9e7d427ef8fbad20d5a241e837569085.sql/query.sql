-- Set some settings
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Table structure for table `ID_team`
CREATE TABLE IF NOT EXISTS `ID_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(400) NOT NULL,
  `uname` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `owner_account_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uname` (`uname`),
  KEY `owner_account_id` (`owner_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Table structure for table `ID_teamAccount`
CREATE TABLE IF NOT EXISTS `ID_teamAccount` (
  `account_id` int(11) unsigned NOT NULL,
  `team_id` int(11) NOT NULL,
  `def` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`,`team_id`),
  KEY `team_id` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Constraints for table `ID_team`
ALTER TABLE `ID_team`
  ADD CONSTRAINT `ID_team_ibfk_1` FOREIGN KEY (`owner_account_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `ID_teamAccount`
ALTER TABLE `ID_teamAccount`
  ADD CONSTRAINT `ID_teamAccount_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `ID_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ID_teamAccount_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `ID_team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;