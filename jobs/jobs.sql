/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : yii2advanced

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2016-11-23 13:47:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(50) DEFAULT NULL,
  `payload` text,
  `attempts` int(5) NOT NULL DEFAULT '0',
  `reserved` tinyint(1) NOT NULL DEFAULT '0',
  `reserved_at` int(10) DEFAULT NULL,
  `available_at` int(10) DEFAULT NULL,
  `created_at` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
