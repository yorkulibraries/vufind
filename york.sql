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

CREATE TABLE IF NOT EXISTS `callnumber_browse_index` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `record_id` varchar(32) NOT NULL,
    `callnum` varchar(64) NOT NULL,
    `shelving_key` varchar(64) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;
CREATE INDEX callnumber_browse_index_record_id ON callnumber_browse_index(record_id);
CREATE INDEX callnumber_browse_index_callnum ON callnumber_browse_index(callnum);

