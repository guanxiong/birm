/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50616
Source Host           : localhost:3306
Source Database       : we7_cheng

Target Server Type    : MYSQL
Target Server Version : 50616
File Encoding         : 65001

Date: 2014-05-16 21:33:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ims_timeaxis_rep
-- ----------------------------
DROP TABLE IF EXISTS `ims_timeaxis_rep`;
CREATE TABLE `ims_timeaxis_rep` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rid` int(10) unsigned NOT NULL,
  `weid` int(10) unsigned NOT NULL,
  `reptitle` varchar(100) NOT NULL DEFAULT '',
  `repinfo` varchar(255) DEFAULT '',
  `repimg` varchar(255) DEFAULT NULL,
  `axisid` int(10) unsigned NOT NULL COMMENT '时光轴活动id',
  PRIMARY KEY (`id`,`rid`,`weid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ims_timeaxis_rep
-- ----------------------------
