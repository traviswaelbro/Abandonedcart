<?php
class Mbs_Abandonedcart_Model_Resource_Abandonedcart_Collection
extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	protected function _construct() {
		$this->_init('abandonedcart/abandonedcart');
	}
}