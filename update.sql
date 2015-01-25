alter table `ims_wechats` add `EncodingAESKey` varchar(43) NOT NULL;
--
-- 下面两条：安装1230完整包的可以不用导入，否则会提示重复！
--
alter table `ims_wechats` add  KEY `idx_parentid` (`parentid`);
alter table `ims_wechats` add  KEY `idx_key` (`key`);
