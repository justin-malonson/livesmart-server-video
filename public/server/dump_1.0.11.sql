DROP TABLE IF EXISTS `lsv_payment_options`;
CREATE TABLE IF NOT EXISTS `lsv_payment_options` (
  `payment_option_id` int(255) NOT NULL AUTO_INCREMENT,
  `paypal_client_id` varchar(255) NOT NULL,
  `paypal_secret_id`  varchar(255) NULL,
  `stripe_client_id`  varchar(255) NULL,
  `stripe_secret_id`  varchar(255) NULL,
  `authorizenet_api_login_id`  varchar(255) NULL,
  `authorizenet_transaction_key`  varchar(255) NULL,
  `authorizenet_public_client_key`  varchar(255) NULL,
  `email_subject` VARCHAR(255) NULL,
  `email_body` TEXT NULL,
  `email_from` VARCHAR(255) NULL,
  `email_notification` tinyint(4) DEFAULT 0 NULL,
  `license`  varchar(255) NULL,
  `is_enabled` tinyint(4) DEFAULT 0 NULL,
  `is_test_mode` tinyint(4) DEFAULT 1 NULL,
  `paypal_enabled` tinyint(4) DEFAULT 0 NULL,
  `stripe_enabled` tinyint(4) DEFAULT 0 NULL,
  `email_day_notify` tinyint(4) DEFAULT 7 NULL,
  `authorizenet_enabled` tinyint(4) DEFAULT 0 NULL,
  PRIMARY KEY (`payment_option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lsv_plans`;
CREATE TABLE `lsv_plans` (
  `plan_id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `price` float(10,2) NOT NULL,
  `interval` enum('D','W','M','Y') COLLATE utf8_unicode_ci NOT NULL COMMENT 'D=Days (1 to 90) | W=Weeks (1 to 52) | M=Months (1 to 24) | Y=Years (1 to 5)',
  `interval_count` tinyint(2) NOT NULL DEFAULT 1,
  `description` TEXT NULL,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `lsv_subscriptions`;
CREATE TABLE `lsv_subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL DEFAULT 0 COMMENT 'foreign key of "users" table',
  `plan_id` int(5) DEFAULT NULL COMMENT 'foreign key of "plans" table',
  `payment_method` enum('paypal', 'stripe', 'authorizenet') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'paypal',
  `payment_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `txn_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subscr_interval` char(1) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'D=Days | W=Weeks | M=Months | Y=Years',
  `subscr_interval_count` int(5) DEFAULT NULL,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime DEFAULT NULL,
  `amount` float(10,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `payer_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payer_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tenant` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_status` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `email_sent` tinyint(4) DEFAULT 0 NULL,
  PRIMARY KEY (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `lsv_payment_options` (`payment_option_id`, `paypal_client_id`, `paypal_secret_id`, `stripe_client_id`, `stripe_secret_id`, `license`, `email_subject`, `email_body`, `email_from`, `is_enabled`) VALUES (NULL, '', NULL, NULL, NULL, NULL, 'Expiring subscription from LiveSmart', 'Hello, {{name}}!\r\n\r\nYour subscription is going to expire in {{date}}\r\n\r\nSincerely,\r\nLiveSmart Team.', 'LiveSmart Team', '0');