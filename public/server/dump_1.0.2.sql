DROP TABLE IF EXISTS `lsv_users`;
CREATE TABLE `lsv_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `name`  varchar(255) NULL,
  `roomId` VARCHAR(255) NULL, 
  `first_name` VARCHAR(255) NULL, 
  `last_name` VARCHAR(255) NULL, 
  `token` VARCHAR(255) NULL, 
  `is_blocked` tinyint(4) DEFAULT 0 NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for `chats`
-- ----------------------------
DROP TABLE IF EXISTS `lsv_chats`;
CREATE TABLE `lsv_chats` (
  `chat_id` int(255) NOT NULL AUTO_INCREMENT,
  `message` varchar(4000) DEFAULT NULL,
  `system` varchar(255) DEFAULT '',
  `to` varchar(255) DEFAULT NULL,
  `from` varchar(255) DEFAULT NULL,
  `agent_id` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `room_id` varchar(255) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;