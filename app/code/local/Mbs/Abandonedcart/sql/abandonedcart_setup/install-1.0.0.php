<?php
Mage::log("Install script loaded!",null,"test.log");
$installer = $this;
$installer->startSetup();
Mage::log("Install script started!",null,"test.log");
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('abandonedcart')};
CREATE TABLE {$this->getTable('abandonedcart')} (
	`email_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`status` varchar(255) NOT NULL,
	`sent_at` datetime NOT NULL,
	PRIMARY KEY (`email_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
Mage::log("Install script installed!",null,"test.log");
$installer->endSetup();
Mage::log("Install script finished!",null,"test.log");
?>