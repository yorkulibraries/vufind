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

CREATE TABLE IF NOT EXISTS `paid_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bib_id` int(11),
  `user_barcode` varchar(14) NOT NULL,
  `item_barcode` varchar(14),
  `item_title` varchar(200),
  `bill_key` varchar(20) NOT NULL,
  `bill_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bill_reason` varchar(100),
  `bill_library` varchar(20) NOT NULL,
  `item_library` varchar(20),
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `payment_auth_code` varchar(100) NOT NULL,
  `user_key` varchar(20) NOT NULL,
  `bill_number` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_key_UNIQUE` (`bill_key`)
) ENGINE=InnoDB;
CREATE INDEX paid_bill_user_barcode_index ON paid_bill (user_barcode);

