--
-- Table structure for table `failed_logins`
--
CREATE TABLE IF NOT EXISTS `failed_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(14) NOT NULL,
  `ip` varchar(19) NOT NULL,
  `last_attempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `attempts` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB;

--
-- Table structure for table `requests`
--
CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `user_barcode` varchar(14) NOT NULL,
  `item_barcode` varchar(14) NOT NULL,
  `item_callnum` varchar(100) NOT NULL,
  `request_type` varchar(100) NOT NULL,
  `pickup_location` varchar(100) NOT NULL,
  `comment` text,
  `expiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ils_hold_created` smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `issns` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `number` varchar(8) NOT NULL,
    `record_id` varchar(32) NOT NULL,
    `source` varchar(16) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `issn_record_id_UNIQUE` (`number`, `record_id`)
) ENGINE=InnoDB;
CREATE INDEX issns_source_index ON issns(source);

CREATE TABLE IF NOT EXISTS `resolver_ids` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `number` varchar(8) NOT NULL,
    `record_id` varchar(32) NOT NULL,
    `source` varchar(16) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `record_id_UNIQUE` (`number`, `record_id`)
) ENGINE=InnoDB;
CREATE INDEX resolver_ids_source_index ON resolver_ids(source);

CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tokenid` varchar(100) NOT NULL,
  `authcode` varchar(8),
  `refnum` varchar(18),
  `ypborderid` varchar(50),
  `payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `payment_hash` varchar(100) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `user_barcode` varchar(14) NOT NULL,
  `fines_group` varchar(40) NOT NULL,
  `notified_user` SMALLINT NOT NULL DEFAULT 0,
  `status` varchar(50),
  `message` varchar(100),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tokenid_UNIQUE` (`tokenid`),
  UNIQUE KEY `authcode_UNIQUE` (`authcode`),
  UNIQUE KEY `refnum_UNIQUE` (`refnum`),
  UNIQUE KEY `payment_hash_UNIQUE` (`payment_hash`)
) ENGINE=InnoDB;
CREATE INDEX payment_token_id_index ON payment (`token_id`);

CREATE TABLE IF NOT EXISTS `paid_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bib_id` int(11),
  `user_key` varchar(20) NOT NULL,
  `user_barcode` varchar(14) NOT NULL,
  `item_barcode` varchar(14),
  `item_title` varchar(200),
  `item_library` varchar(20),
  `bill_key` varchar(20) NOT NULL,
  `bill_number` int(11) NOT NULL,
  `bill_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bill_reason` varchar(100),
  `bill_library` varchar(20) NOT NULL,
  `bill_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_id` int(11) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `api_response` varchar(1000),
  `api_request` varchar(1000),
  `api_successful` smallint NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY(`payment_id`) REFERENCES payment(`id`),
  UNIQUE KEY `bill_key_UNIQUE` (`bill_key`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `translation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` varchar(2) NOT NULL,
  `key` varchar(250) NOT NULL,
  `value` varchar(4000),
  `last_modified_by` varchar(14),
  `verified` smallint NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_lang_UNIQUE` (`key`, `lang`)
) ENGINE=InnoDB;


