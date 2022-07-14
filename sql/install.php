<?php

if ( ! defined('_PS_VERSION_')) {
    exit;
}

$query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'credit_key` (
			  `id_cart` INT(11) NOT NULL,
			  `id_order` INT(11) NOT NULL,
			  `transaction_id` VARCHAR(50),
			  `refund_status` INT(1) DEFAULT 0,
			  `cancel_status` INT(1) DEFAULT 0,
			  `refund_amount` DECIMAL( 10, 2 ) DEFAULT 0,
			  `grand_total` DECIMAL( 10, 2 ) NOT NULL,
			  PRIMARY KEY (`id_cart`)
			) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;");';

if (Db::getInstance()->execute($query) == false) {
    return false;
}