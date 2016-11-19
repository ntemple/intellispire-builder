ALTER TABLE `purchase_ipn` CHANGE `source` `pdc_source` ENUM( 'paypal', 'stormpay' ) NULL DEFAULT NULL;

ALTER TABLE `purchase_ipn` ADD `pdc_affid` VARCHAR( 50 ) AFTER `pdc_source` ,
ADD `pdc_productid` INT AFTER `pdc_affid` ,
ADD `pdc_campid` INT AFTER `pdc_productid` ,
ADD `pdc_tool` INT AFTER `pdc_campid` ,
ADD `pdc_session` VARCHAR( 50 ) AFTER `pdc_tool` ;

ALTER TABLE `purchase_session` ADD `ipn_count` INT NOT NULL ;
ALTER TABLE `purchase_session` ADD `ipn_url` VARCHAR( 255 ) ;

ALTER TABLE `purchase_session` ADD `pdc_profit` DECIMAL( 10, 2 ) ,
ADD `pdc_fee` DECIMAL( 10, 2 ) ,
ADD `pdc_secret` VARCHAR( 255 ) ,
ADD `pdc_affiliate` VARCHAR( 255 ) ;

ALTER TABLE `purchase_ipn` ADD `subscr_id` VARCHAR( 255 ) AFTER `txn_id` ;

ALTER TABLE `purchase_sales` ADD `session_id` INT AFTER `txn_id` ;

ALTER TABLE `purchase_session`
  DROP `ipn_count`,
  DROP `ipn_url`,
  DROP `pdc_profit`,
  DROP `pdc_fee`,
  DROP `pdc_secret`,
  DROP `pdc_affiliate`;

DROP TABLE IF EXISTS `pdc_ipn`;
CREATE TABLE `pdc_ipn` (
  `id` int(11) NOT NULL auto_increment,
  `ts` timestamp NOT NULL default '0000-00-00 00:00:00',
  `ipn_id` int(11) default NULL,
  `sale_id` int(11) default NULL,
  `session_id` int(11) default NULL,
  `pdc_secret` varchar(255) default NULL,
  `ipn_url` varchar(255) default NULL,
  `count` int(11) NOT NULL default '0',
  `status` enum('pending','done') NOT NULL default 'pending',
  PRIMARY KEY  (`id`)
);

