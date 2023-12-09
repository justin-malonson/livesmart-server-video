DROP TABLE IF EXISTS `lsv_rooms`;
CREATE TABLE `lsv_rooms` (
`room_id`  int(11) NOT NULL AUTO_INCREMENT ,
`agent`  varchar(255) NULL ,
`visitor`  varchar(255) NULL ,
`agenturl`  varchar(2048) NULL ,
`visitorurl`  varchar(2048) NULL ,
`password`  varchar(255) NULL ,
`roomId`  varchar(255) NULL ,
`datetime`  varchar(255) NULL ,
`duration`  varchar(255) NULL ,
`shortagenturl`  varchar(255) NULL,
`shortvisitorurl`  varchar(255) NULL,
`agent_id`  varchar(255) NULL,
`is_active` TINYINT NOT NULL DEFAULT '1',
`agenturl_broadcast`  varchar(2048) NULL,
`visitorurl_broadcast`  varchar(2048) NULL ,
`shortagenturl_broadcast`  varchar(2048) NULL,
`shortvisitorurl_broadcast`  varchar(2048) NULL,
`title` VARCHAR(2048) NULL,
PRIMARY KEY (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lsv_agents`;
CREATE TABLE `lsv_agents` (
  `agent_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `is_master` tinyint(4) NOT NULL DEFAULT '0',
  `roomId` VARCHAR(255) NULL, 
  `token` VARCHAR(255) NULL,
  PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `lsv_agents` ADD `recovery_token` VARCHAR(255) NULL AFTER `token`, ADD `date_expired` DATETIME NULL AFTER `recovery_token`;

-- ----------------------------
-- Records of agents
-- ----------------------------
INSERT INTO `lsv_agents` VALUES ('1', 'admin', 'first', 'last', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@admin.com', '1', '', '', null, null);
