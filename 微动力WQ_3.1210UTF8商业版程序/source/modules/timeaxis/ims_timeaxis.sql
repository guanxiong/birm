/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50616
Source Host           : localhost:3306
Source Database       : we7_cheng

Target Server Type    : MYSQL
Target Server Version : 50616
File Encoding         : 65001

Date: 2014-05-16 21:33:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ims_timeaxis
-- ----------------------------
DROP TABLE IF EXISTS `ims_timeaxis`;
CREATE TABLE `ims_timeaxis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `weid` int(10) unsigned NOT NULL,
  `title` varchar(50) NOT NULL COMMENT '活动名称',
  `time` int(11) unsigned NOT NULL,
  `bgimg` varchar(255) NOT NULL COMMENT '背景图片或颜色',
  `bgcol` varchar(30) NOT NULL COMMENT '内容背景色',
  `items` varchar(5000) NOT NULL,
  PRIMARY KEY (`id`,`weid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ims_timeaxis
-- ----------------------------
INSERT INTO `ims_timeaxis` VALUES ('4', '3', '', '1400226304', '', '', 'a:1:{i:0;a:4:{s:4:\"type\";s:2:\"0/\";s:5:\"title\";s:6:\"121212\";s:6:\"direct\";s:0:\"\";s:6:\"detail\";s:10:\"fewvw13213\";}}');
INSERT INTO `ims_timeaxis` VALUES ('5', '3', '', '1400226477', '', '', 'a:1:{i:0;a:4:{s:4:\"type\";s:2:\"0/\";s:5:\"title\";s:5:\"fwefw\";s:6:\"direct\";s:0:\"\";s:6:\"detail\";s:14:\"23va245t23r23f\";}}');
INSERT INTO `ims_timeaxis` VALUES ('6', '3', '', '1400226835', '', '', 'a:1:{i:0;a:4:{s:4:\"type\";s:2:\"0/\";s:5:\"title\";s:6:\"fefwef\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:15:\"234r2f3fa4qfsef\";}}');
INSERT INTO `ims_timeaxis` VALUES ('7', '3', '', '1400232513', '', '', 'a:3:{i:0;a:4:{s:4:\"type\";s:1:\"0\";s:5:\"title\";s:12:\"烦人烦人\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:12:\"啊啊服务\";}i:1;a:4:{s:4:\"type\";s:1:\"1\";s:5:\"title\";s:0:\"\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:9:\"分为非\";}i:2;a:4:{s:4:\"type\";s:1:\"2\";s:5:\"title\";s:10:\"阿飞飞v\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:51:\"images/3/2014/05/AEwo252P5moVNAYeWM55aFIX4Yfw42.jpg\";}}');
INSERT INTO `ims_timeaxis` VALUES ('8', '3', 'name', '1400243151', 'images/3/2014/05/AEwo252P5moVNAYeWM55aFIX4Yfw42.jpg', '#ff9900', 'a:3:{i:0;a:4:{s:4:\"type\";s:1:\"0\";s:5:\"title\";s:12:\"烦人烦人\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:6:\"那么\";}i:1;a:4:{s:4:\"type\";s:1:\"1\";s:5:\"title\";s:6:\"标题\";s:6:\"direct\";s:1:\"l\";s:6:\"detail\";s:276:\"那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始那么开始\";}i:2;a:4:{s:4:\"type\";s:1:\"2\";s:5:\"title\";s:10:\"阿飞飞v\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:51:\"images/3/2014/05/AEwo252P5moVNAYeWM55aFIX4Yfw42.jpg\";}}');
INSERT INTO `ims_timeaxis` VALUES ('9', '3', 'name', '1400236813', 'images/3/2014/05/AEwo252P5moVNAYeWM55aFIX4Yfw42.jpg', '#ff9900', 'a:5:{i:0;a:4:{s:4:\"type\";s:1:\"0\";s:5:\"title\";s:12:\"烦人烦人\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:0:\"\";}i:1;a:4:{s:4:\"type\";s:1:\"1\";s:5:\"title\";s:0:\"\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:0:\"\";}i:2;a:4:{s:4:\"type\";s:1:\"2\";s:5:\"title\";s:10:\"阿飞飞v\";s:6:\"direct\";s:1:\"r\";s:6:\"detail\";s:51:\"images/3/2014/05/AEwo252P5moVNAYeWM55aFIX4Yfw42.jpg\";}i:3;a:4:{s:4:\"type\";s:1:\"0\";s:5:\"title\";s:12:\"分额访问\";s:6:\"direct\";s:1:\"l\";s:6:\"detail\";s:0:\"\";}i:4;a:4:{s:4:\"type\";s:1:\"0\";s:5:\"title\";s:5:\"fever\";s:6:\"direct\";s:1:\"l\";s:6:\"detail\";s:6:\"那么\";}}');
