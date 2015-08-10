<?php
class Mbs_Abandonedcart_Model_Resource_Abandonedcart
extends Mage_Core_Model_Resource_Db_Abstract {
	public function _construct() {
		$this->_init('abandonedcart/abandonedcart', 'email_id');
	}
}